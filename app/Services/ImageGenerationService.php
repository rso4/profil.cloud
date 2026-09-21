<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * ImageGenerationService
 *
 * Bertanggung jawab menghasilkan aset gambar statis untuk template dan galeri.
 *
 * Alur:
 *  1. Coba unduh gambar dari API text_to_image eksternal.
 *  2. Jika API gagal ATAU mengembalikan placeholder rusak (MD5 diketahui),
 *     generate gambar mockup lokal yang unik per slug via intervention/image v3.
 *  3. Simpan 3 varian (thumb, hero, full) ke disk "public" Storage Laravel.
 *
 * Semua path menggunakan Storage::disk('public') dan helper Laravel
 * (storage_path/public_path tidak di-hardcode).
 */
class ImageGenerationService
{
    /** Varian yang dihasilkan per template: variant => [width, height, quality]. */
    public const VARIANTS = [
        'thumb' => [600, 450, 80],   // Thumbnail card/list (4:3)
        'hero'  => [1920, 1080, 82], // Hero image full-width (16:9)
        'full'  => [1280, 960, 85],  // Gambar konten resolusi tinggi
    ];

    /** MD5 placeholder yang dikembalikan API eksternal saat degraded. */
    protected const BROKEN_PLACEHOLDER_MD5 = '19a0b822edb11957055e4588c2159058';

    /** Font TTF untuk teks pada mockup/artwork. */
    protected const FONT_PATH = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

    /**
     * Generate semua varian untuk satu slug dan prompt.
     *
     * @param  string $slug      Identifier template (mis. hotel-01)
     * @param  string $prompt    Teks prompt untuk text_to_image API
     * @param  string $imageSize Aspek rasio API: landscape_4_3, landscape_16_9, square
     * @return array{variants: array<string,string>, dynamic_url: string, source: string}
     */
    public function generateForTemplate(string $slug, string $prompt, string $imageSize = 'landscape_4_3'): array
    {
        $apiUrl = $this->buildDynamicUrl($prompt, $imageSize);
        $imageData = $this->download($apiUrl);
        $source = 'api';

        if ($imageData === null || md5($imageData) === self::BROKEN_PLACEHOLDER_MD5) {
            // API eksternal tidak tersedia atau degraded (mengembalikan placeholder
            // yang sama untuk semua prompt) -> generate mockup lokal yang unik.
            $imageData = null;
            $source = 'local';
            Log::info("Preview image generated locally for '{$slug}' (external API unavailable).");
        }

        $variants = [];

        foreach (self::VARIANTS as $variant => [$width, $height, $quality]) {
            $path = "images/templates/{$slug}/{$variant}.jpg";

            if ($imageData !== null) {
                $stored = $this->storeResizedFromBinary($imageData, $path, $width, $height, $quality);
            } else {
                $label = $this->labelFromSlug($slug);
                $stored = $this->storePreviewMockup($path, $label, $width, $height, $quality, $this->paletteForSlug($slug));
            }

            if ($stored) {
                $variants[$variant] = Storage::disk('public')->url($path);
            }
        }

        return ['variants' => $variants, 'dynamic_url' => $apiUrl, 'source' => $source];
    }

    /**
     * Generate gambar artwork abstrak untuk kebutuhan galeri/demo.
     *
     * @param  string $path   Path relatif pada disk public (mis. galleries/1/demo-1.jpg)
     * @param  string $label  Label teks yang ditampilkan
     * @param  int     $width  Lebar gambar
     * @param  int     $height Tinggi gambar
     * @param  int     $quality Kualitas JPEG (0-100)
     * @return bool True jika berhasil disimpan
     */
    public function generateArtwork(string $path, string $label, int $width = 1280, int $height = 960, int $quality = 85): bool
    {
        try {
            $seed = $label . '|' . $path;
            $palette = $this->paletteFromString($seed);

            $image = $this->createGradientImage($palette, $width, $height);
            $this->paintDecorativeShapes($image, $palette, $width, $height, $seed);

            if ($label !== '') {
                $this->paintText($image, $label, (int) ($width / 2), (int) ($height / 2), max(16, (int) ($width * 0.06)), '#ffffff');
            }

            return $this->saveToPublicDisk($image, $path, $quality);
        } catch (\Throwable $e) {
            Log::error("generateArtwork error '{$path}': {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Buat thumbnail optimal dari file gambar yang diupload (untuk galeri).
     *
     * @param  string      $sourcePath Path relatif file asli pada disk public
     * @param  string      $targetPath Path relatif thumbnail tujuan
     * @param  int         $width  Lebar thumbnail
     * @param  int         $height Tinggi thumbnail
     * @param  int         $quality Kualitas JPEG
     * @return bool True jika berhasil
     */
    public function createThumbnail(string $sourcePath, string $targetPath, int $width = 600, int $height = 450, int $quality = 80): bool
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($sourcePath)) {
            Log::warning("createThumbnail: source tidak ditemukan '{$sourcePath}'.");
            return false;
        }

        try {
            $binary = $disk->get($sourcePath);
            $image = Image::read($binary)->cover($width, $height);

            return $this->saveToPublicDisk($image, $targetPath, $quality);
        } catch (\Throwable $e) {
            Log::error("createThumbnail error '{$sourcePath}': {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Bangun URL text_to_image dengan prompt ter-encode.
     */
    public function buildDynamicUrl(string $prompt, string $imageSize = 'landscape_4_3'): string
    {
        $base = config('images.dynamic_url', 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image');

        return $base . '?prompt=' . urlencode($prompt) . '&image_size=' . $imageSize;
    }

    /**
     * Download gambar ke memory sebagai binary string.
     */
    protected function download(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'User-Agent' => 'ProfilCloud/1.0',
            ])->get($url);

            if (!$response->successful()) {
                Log::warning("Image download non-2xx: HTTP {$response->status()}");

                return null;
            }

            $body = $response->body();

            if (strlen($body) < 100) {
                return null;
            }

            return $body;
        } catch (\Throwable $e) {
            Log::error("Image download error: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Simpan gambar hasil resize dari binary API ke disk public.
     */
    protected function storeResizedFromBinary(string $binary, string $path, int $width, int $height, int $quality): bool
    {
        try {
            $image = Image::read($binary)->cover($width, $height);

            return $this->saveToPublicDisk($image, $path, $quality);
        } catch (\Throwable $e) {
            Log::error("storeResizedFromBinary error '{$path}': {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Generate mockup preview website yang unik per template.
     */
    protected function storePreviewMockup(string $path, string $label, int $width, int $height, int $quality, array $palette): bool
    {
        try {
            $image = $this->createGradientImage($palette, $width, $height);

            // 1. Bentuk dekoratif translucent
            $this->paintDecorativeShapes($image, $palette, $width, $height, $label);

            // 2. Jendela browser mockup
            $this->paintBrowserWindow($image, $palette, $width, $height, $label);

            // 3. Label nama template
            $this->paintText($image, strtoupper($label), (int) ($width / 2), (int) ($height * 0.94), max(16, (int) ($width * 0.035)), '#ffffff');

            return $this->saveToPublicDisk($image, $path, $quality);
        } catch (\Throwable $e) {
            Log::error("storePreviewMockup error '{$path}': {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Buat canvas gradient vertikal dua warna (via GD, interpolasi per baris),
     * lalu kembalikan sebagai objek gambar intervention/image v3.
     */
    protected function createGradientImage(array $palette, int $width, int $height)
    {
        $gd = imagecreatetruecolor($width, $height);

        [$r1, $g1, $b1] = $this->hexToRgb($palette['primaryDark']);
        [$r2, $g2, $b2] = $this->hexToRgb($palette['primary']);

        for ($y = 0; $y < $height; $y++) {
            $t = $height > 1 ? $y / ($height - 1) : 0;
            $color = imagecolorallocate(
                $gd,
                (int) round($r1 + ($r2 - $r1) * $t),
                (int) round($g1 + ($g2 - $g1) * $t),
                (int) round($b1 + ($b2 - $b1) * $t)
            );
            imagefilledrectangle($gd, 0, $y, $width, $y, $color);
        }

        ob_start();
        imagejpeg($gd, null, 95);
        $binary = (string) ob_get_clean();
        imagedestroy($gd);

        return Image::read($binary);
    }

    /**
     * Gambar bentuk dekoratif (lingkaran besar translucent) deterministik dari seed.
     */
    protected function paintDecorativeShapes($image, array $palette, int $width, int $height, string $seed): void
    {
        $hash = crc32($seed);

        $circles = [
            ['x' => $width * 0.85, 'y' => $height * 0.15, 'size' => $width * 0.45, 'color' => $palette['accent'], 'alpha' => 30],
            ['x' => $width * 0.10, 'y' => $height * 0.85, 'size' => $width * 0.35, 'color' => $palette['accentLight'], 'alpha' => 22],
            ['x' => $width * (0.3 + ($hash % 40) / 100), 'y' => $height * 0.05, 'size' => $width * 0.20, 'color' => $palette['accentLight'], 'alpha' => 18],
        ];

        foreach ($circles as $circle) {
            $image->drawEllipse((int) $circle['x'], (int) $circle['y'], function ($draw) use ($circle) {
                $draw->size((int) $circle['size'], (int) $circle['size']);
                $draw->background($this->withAlpha($circle['color'], $circle['alpha']));
            });
        }
    }

    /**
     * Gambar jendela browser mockup dengan hero banner dan kartu konten.
     */
    protected function paintBrowserWindow($image, array $palette, int $width, int $height, string $label): void
    {
        $marginX = (int) ($width * 0.07);
        $top = (int) ($height * 0.10);
        $winW = $width - ($marginX * 2);
        $winH = (int) ($height * 0.74);

        // Frame jendela (putih)
        $image->drawRectangle($marginX, $top, function ($rect) use ($winW, $winH) {
            $rect->size($winW, $winH);
            $rect->background('#ffffff');
            $rect->border('#e2e8f0', 1);
        });

        // Bar atas browser
        $barH = max(14, (int) ($height * 0.05));
        $image->drawRectangle($marginX, $top, function ($rect) use ($winW, $barH) {
            $rect->size($winW, $barH);
            $rect->background('#f1f5f9');
        });

        // Tiga titik window control
        $dotY = $top + (int) ($barH / 2);
        $dotR = max(3, (int) ($barH * 0.16));
        $dotColors = ['#ef4444', '#f59e0b', '#22c55e'];
        foreach ($dotColors as $i => $color) {
            $image->drawEllipse($marginX + (int) ($barH * 0.9) + ($i * (int) ($barH * 0.75)), $dotY, function ($draw) use ($dotR, $color) {
                $draw->size($dotR * 2, $dotR * 2);
                $draw->background($color);
            });
        }

        // Area konten dalam jendela
        $contentTop = $top + $barH + (int) ($height * 0.02);
        $contentX = $marginX + (int) ($winW * 0.04);
        $contentW = $winW - (int) ($winW * 0.08);

        // Hero banner
        $heroH = (int) ($winH * 0.38);
        $image->drawRectangle($contentX, $contentTop, function ($rect) use ($contentW, $heroH, $palette) {
            $rect->size($contentW, $heroH);
            $rect->background($this->withAlpha($palette['accent'], 230));
        });

        // Teks hero (dua baris bar putih)
        $lineW1 = (int) ($contentW * 0.62);
        $lineW2 = (int) ($contentW * 0.40);
        $lineH = max(4, (int) ($heroH * 0.10));
        $lineY1 = $contentTop + (int) ($heroH * 0.38);
        $lineY2 = $lineY1 + $lineH + (int) ($heroH * 0.14);
        foreach ([[$lineY1, $lineW1], [$lineY2, $lineW2]] as [$y, $w]) {
            $image->drawRectangle($contentX + (int) ($contentW * 0.06), $y, function ($rect) use ($w, $lineH) {
                $rect->size($w, $lineH);
                $rect->background('#ffffff');
            });
        }

        // Tiga kartu konten
        $cardTop = $contentTop + $heroH + (int) ($winH * 0.05);
        $cardH = (int) ($winH * 0.22);
        $gap = (int) ($contentW * 0.03);
        $cardW = (int) (($contentW - ($gap * 2)) / 3);
        $cardColors = [$palette['accent'], $palette['primary'], $palette['accentLight']];
        foreach ($cardColors as $i => $color) {
            $x = $contentX + ($i * ($cardW + $gap));
            $image->drawRectangle($x, $cardTop, function ($rect) use ($cardW, $cardH, $color) {
                $rect->size($cardW, $cardH);
                $rect->background($this->withAlpha($color, 200));
            });
        }
    }

    /**
     * Gambar teks dengan font TTF dan efek bayangan halus.
     */
    protected function paintText($image, string $text, int $x, int $y, int $size, string $color): void
    {
        $image->text($text, $x + 2, $y + 2, function ($font) use ($size) {
            $font->filename(self::FONT_PATH);
            $font->size($size);
            $font->color('rgba(0,0,0,0.35)');
            $font->align('center');
            $font->valign('middle');
        });

        $image->text($text, $x, $y, function ($font) use ($size, $color) {
            $font->filename(self::FONT_PATH);
            $font->size($size);
            $font->color($color);
            $font->align('center');
            $font->valign('middle');
        });
    }

    /**
     * Simpan objek gambar intervention ke disk public dengan kualitas JPEG.
     */
    protected function saveToPublicDisk($image, string $path, int $quality): bool
    {
        try {
            $disk = Storage::disk('public');
            $directory = dirname($path);

            if ($directory !== '.' && !$disk->exists($directory)) {
                $disk->makeDirectory($directory);
            }

            $disk->put($path, (string) $image->toJpeg($quality));

            return true;
        } catch (\Throwable $e) {
            Log::error("saveToPublicDisk error '{$path}': {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Ubah slug template menjadi label yang mudah dibaca (hotel-01 -> Hotel 01).
     */
    protected function labelFromSlug(string $slug): string
    {
        return ucfirst(str_replace(['-', '_'], ' ', $slug));
    }

    /**
     * Palet warna deterministik per slug, dibatasi per kategori agar tematik:
     * hotel (biru), school (hijau), sme (oranye/ungu).
     */
    protected function paletteForSlug(string $slug): array
    {
        return $this->paletteFromString($slug);
    }

    /**
     * Palet warna deterministik dari string seed.
     */
    protected function paletteFromString(string $seed): array
    {
        $category = str_starts_with($seed, 'hotel') ? 'hotel'
            : (str_starts_with($seed, 'school') ? 'school' : 'sme');

        $baseHue = match ($category) {
            'hotel' => 212,
            'school' => 150,
            default => 26,
        };

        $hash = crc32($seed);
        $hue = ($baseHue + ($hash % 70) - 35 + 360) % 360;
        $hue2 = ($hue + 35 + (($hash >> 8) % 45)) % 360;

        return [
            'primary'      => $this->hslToHex($hue, 58, 44),
            'primaryDark'  => $this->hslToHex($hue, 52, 24),
            'accent'       => $this->hslToHex($hue2, 72, 56),
            'accentLight'  => $this->hslToHex($hue2, 82, 74),
        ];
    }

    /**
     * Konversi HSL ke HEX.
     */
    protected function hslToHex(float $h, float $s, float $l): string
    {
        $s /= 100;
        $l /= 100;
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - ($c / 2);

        $h = (int) $h;
        [$r, $g, $b] = match (true) {
            $h < 60  => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default  => [$c, 0, $x],
        };

        $r = (int) round(($r + $m) * 255);
        $g = (int) round(($g + $m) * 255);
        $b = (int) round(($b + $m) * 255);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Tambahkan alpha (0-255) ke warna hex -> format rgba intervention.
     */
    protected function withAlpha(string $hex, int $alpha): string
    {
        [$r, $g, $b] = $this->hexToRgb($hex);

        return sprintf('rgba(%d,%d,%d,%d)', $r, $g, $b, $alpha);
    }

    /**
     * Konversi warna hex ke array [r, g, b].
     *
     * @return array{0: int, 1: int, 2: int}
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
