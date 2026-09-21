<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * PexelsService
 *
 * Mengunduh gambar berkualitas tinggi dari Pexels API dan menyimpannya
 * sebagai aset statis template (thumb, hero, full) di disk "public".
 *
 * Semua gambar Pexels dilisensikan di bawah Pexels License yang mengizinkan
 * penggunaan gratis untuk komersial dan non-komersial tanpa atribusi wajib.
 *
 * Alur:
 *  1. Cari foto via Pexels API menggunakan query per template.
 *  2. Unduh foto resolusi tinggi (original / large2x).
 *  3. Simpan 3 varian (thumb, hero, full) ke disk "public" Storage Laravel.
 */
class PexelsService
{
    /** Varian yang dihasilkan per template: variant => [width, height, quality]. */
    public const VARIANTS = [
        'thumb' => [600, 450, 80],   // Thumbnail card/list (4:3)
        'hero'  => [1920, 1080, 82], // Hero image full-width (16:9)
        'full'  => [1280, 960, 85],  // Gambar konten resolusi tinggi
    ];

    /** Base URL API Pexels. */
    protected const API_URL = 'https://api.pexels.com/v1';

    /**
     * Generate semua varian gambar untuk satu slug dari Pexels.
     *
     * @param  string $slug Identifier template (mis. hotel-01)
     * @return array{variants: array<string,string>, source: string, photo_id: ?int, photographer: ?string}
     */
    public function generateForTemplate(string $slug): array
    {
        $queries = config("pexels.queries.{$slug}", []);
        $heroQuery = $queries['hero'] ?? $slug;
        $contentQueries = $queries['content'] ?? [];

        // Cari foto hero (query utama)
        $heroPhoto = $this->searchPhoto($heroQuery, 'landscape');

        if ($heroPhoto === null) {
            Log::warning("Pexels: tidak ada foto untuk '{$slug}' (query: {$heroQuery}).");

            return ['status' => 'failed', 'variants' => [], 'public' => '', 'photos' => []];
        }

        // Cari foto konten tambahan (untuk galeri)
        $contentPhotos = [];
        foreach ($contentQueries as $query) {
            $photo = $this->searchPhoto($query, 'landscape');
            if ($photo !== null) {
                $contentPhotos[] = $photo;
            }
        }

        // Pastikan minimal 4 galeri; isi kekurangan dengan foto hero.
        while (count($contentPhotos) < 4) {
            $contentPhotos[] = $heroPhoto;
        }

        // Simpan varian utama (thumb, hero, full) dari foto hero
        $variants = [];
        foreach (self::VARIANTS as $variant => [$width, $height, $quality]) {
            $path = "images/templates/{$slug}/{$variant}.jpg";
            $stored = $this->storeFromPhoto($heroPhoto, $path, $width, $height, $quality);
            if ($stored) {
                $variants[$variant] = Storage::disk('public')->url($path);
            }
        }

        // Simpan foto konten sebagai galeri (gallery-1..N)
        $galleryPaths = [];
        foreach ($contentPhotos as $i => $photo) {
            $path = "images/templates/{$slug}/gallery-" . ($i + 1) . '.jpg';
            $stored = $this->storeFromPhoto($photo, $path, 1280, 960, 85);
            if ($stored) {
                $galleryPaths[] = Storage::disk('public')->url($path);
            }
        }

        return [
            'status' => 'success',
            'variants' => $variants,
            'public' => Storage::disk('public')->url("images/templates/{$slug}/thumb.jpg"),
            'photos' => $galleryPaths,
            'photo_id' => $heroPhoto['id'] ?? null,
            'photographer' => $heroPhoto['photographer'] ?? null,
        ];
    }

    /**
     * Cari satu foto dari Pexels berdasarkan query dan orientasi.
     *
     * @return array{id: int, url: string, photographer: string, src: array}|null
     */
    protected function searchPhoto(string $query, string $orientation = 'landscape'): ?array
    {
        $apiKey = config('pexels.api_key');
        if (empty($apiKey)) {
            Log::error('Pexels: API key tidak dikonfigurasi (config pexels.api_key).');
            return null;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => $apiKey])
                ->get(self::API_URL . '/search', [
                    'query' => $query,
                    'orientation' => $orientation,
                    'per_page' => 1,
                    'page' => 1,
                ]);

            if (!$response->successful()) {
                Log::warning("Pexels search non-2xx: HTTP {$response->status()} untuk query '{$query}'.");
                return null;
            }

            $data = $response->json();
            $photos = $data['photos'] ?? [];

            if (empty($photos)) {
                Log::warning("Pexels: tidak ada hasil untuk query '{$query}'.");
                return null;
            }

            $photo = $photos[0];

            return [
                'id' => $photo['id'] ?? null,
                'url' => $photo['url'] ?? null,
                'photographer' => $photo['photographer'] ?? null,
                'src' => $photo['src'] ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error("Pexels search error '{$query}': {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Unduh dan simpan foto dari Pexels ke disk public dengan resize.
     */
    protected function storeFromPhoto(array $photo, string $path, int $width, int $height, int $quality): bool
    {
        // Pilih URL resolusi tinggi (large2x atau original)
        $src = $photo['src'] ?? [];
        $url = $src['large2x'] ?? $src['original'] ?? null;

        if (empty($url)) {
            Log::warning('Pexels: foto tidak memiliki URL sumber.');
            return false;
        }

        try {
            $binary = $this->download($url);
            if ($binary === null) {
                return false;
            }

            $image = Image::read($binary)->cover($width, $height);

            return $this->saveToDisk($image, $path, $quality);
        } catch (\Throwable $e) {
            Log::error("Pexels store error '{$path}': {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Download gambar ke memory sebagai binary string.
     */
    protected function download(string $url): ?string
    {
        try {
            $response = Http::timeout(60)->withHeaders([
                'User-Agent' => 'ProfilCloud/1.0',
            ])->get($url);

            if (!$response->successful()) {
                Log::warning("Pexels download non-2xx: HTTP {$response->status()}");
                return null;
            }

            $body = $response->body();
            if (strlen($body) < 100) {
                return null;
            }

            return $body;
        } catch (\Throwable $e) {
            Log::error("Pexels download error: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Simpan objek gambar intervention ke disk public dengan kualitas JPEG.
     */
    protected function saveToDisk($image, string $path, int $quality): bool
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
            Log::error("Pexels saveToDisk error '{$path}': {$e->getMessage()}");
            return false;
        }
    }
}
