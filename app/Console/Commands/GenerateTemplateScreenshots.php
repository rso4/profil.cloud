<?php

namespace App\Console\Commands;

use App\Models\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Generate screenshot thumbnail & fullscreen dari template website yang
 * dirender dengan data dummy, menggunakan Chromium headless.
 *
 * Usage:
 *   php artisan templates:screenshot                  # Semua template
 *   php artisan templates:screenshot hotel-01         # Slug tertentu
 *   php artisan templates:screenshot --regenerate     # Hapus & regenerate
 */
class GenerateTemplateScreenshots extends Command
{
    protected $signature = 'templates:screenshot
                            {slug? : Slug template tertentu (opsional)}
                            {--regenerate : Hapus file lama sebelum generate ulang}';

    protected $description = 'Generate screenshot thumbnail & fullscreen dari template website via Chromium headless';

    /** Ukuran viewport untuk screenshot. */
    protected const VIEWPORT = [1280, 960];

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $regenerate = $this->option('regenerate');

        $query = Template::query();
        if ($slug !== null) {
            $query->where('slug', $slug);
        }
        $templates = $query->get();

        if ($templates->isEmpty()) {
            $this->error('Tidak ada template yang cocok.');

            return self::FAILURE;
        }

        $token = config('app.preview_token');
        $baseUrl = rtrim(config('app.url'), '/');
        $chrome = $this->chromeBinary();

        if (!$chrome) {
            $this->error('Chromium tidak ditemukan. Install chromium atau set CHROME_BINARY.');

            return self::FAILURE;
        }

        $this->info("Menggunakan Chromium: {$chrome}");
        $this->info("Memulai screenshot untuk " . $templates->count() . " template...");
        $bar = $this->output->createProgressBar($templates->count());
        $bar->start();

        $generated = 0;
        $failed = [];

        foreach ($templates as $template) {
            $bar->advance();

            if ($regenerate) {
                $this->wipe($template->slug);
            }

            $url = "{$baseUrl}/preview-template/{$template->slug}?token={$token}";

            try {
                $ok = $this->capture($chrome, $url, $template->slug);

                if ($ok) {
                    $generated++;
                } else {
                    $failed[] = $template->slug;
                }
            } catch (\Throwable $e) {
                $this->error("\nError screenshot {$template->slug}: {$e->getMessage()}");
                $failed[] = $template->slug;
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Selesai. Berhasil: {$generated} template");

        if (!empty($failed)) {
            $this->warn('Gagal: ' . implode(', ', $failed));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Ambil screenshot thumbnail (600x450) dan fullscreen (1280x960).
     */
    protected function capture(string $chrome, string $url, string $slug): bool
    {
        $disk = Storage::disk('public');
        $dir = "images/templates/{$slug}";
        $disk->makeDirectory($dir);

        $profile = sys_get_temp_dir() . '/chrome-profile-' . uniqid();
        @mkdir($profile, 0777, true);

        $variants = [
            'preview-thumb' => [600, 450],
            'preview-full' => [1280, 960],
        ];

        $allOk = true;

        foreach ($variants as $variant => [$width, $height]) {
            $tmp = sys_get_temp_dir() . "/{$slug}-{$variant}-" . uniqid() . '.png';
            $cmd = sprintf(
                '%s --headless --no-sandbox --disable-gpu --user-data-dir=%s --hide-scrollbars --window-size=%d,%d --screenshot=%s %s 2>&1',
                escapeshellarg($chrome),
                escapeshellarg($profile),
                $width,
                $height,
                escapeshellarg($tmp),
                escapeshellarg($url)
            );

            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0 || !file_exists($tmp) || filesize($tmp) < 1000) {
                $this->warn("  Screenshot {$variant} gagal untuk {$slug}");
                $allOk = false;
                continue;
            }

            $disk->put("{$dir}/{$variant}.jpg", (string) Image::read(file_get_contents($tmp))->toJpeg(85));
            @unlink($tmp);
        }

        @array_map('unlink', glob("{$profile}/*") ?: []);
        @rmdir($profile);

        return $allOk;
    }

    /**
     * Lokasi binary Chromium.
     */
    protected function chromeBinary(): ?string
    {
        $env = getenv('CHROME_BINARY');
        if ($env && file_exists($env)) {
            return $env;
        }

        $candidates = [
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Hapus file preview lama untuk slug tertentu (tidak menghapus gambar Pexels).
     */
    protected function wipe(string $slug): void
    {
        $dir = storage_path('app/public/images/templates/' . $slug);

        if (is_dir($dir)) {
            foreach (['preview-thumb.jpg', 'preview-full.jpg'] as $file) {
                @unlink($dir . '/' . $file);
            }
        }
    }
}
