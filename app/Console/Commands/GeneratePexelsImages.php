<?php

namespace App\Console\Commands;

use App\Models\Template;
use App\Services\PexelsService;
use Illuminate\Console\Command;

/**
 * Pre-generate gambar statis dari Pexels untuk semua template (atau slug tertentu).
 *
 * Usage:
 *   php artisan pexels:images                  # Semua template
 *   php artisan pexels:images hotel-01         # Slug tertentu
 *   php artisan pexels:images --regenerate     # Hapus & regenerate
 */
class GeneratePexelsImages extends Command
{
    protected $signature = 'pexels:images
                            {slug? : Slug template tertentu (opsional)}
                            {--regenerate : Hapus file lama sebelum generate ulang}';

    protected $description = 'Unduh gambar berkualitas tinggi dari Pexels untuk semua template';

    public function handle(PexelsService $service): int
    {
        $slug = $this->argument('slug');
        $regenerate = $this->option('regenerate');

        $queries = config('pexels.queries', []);

        if ($slug !== null) {
            if (!isset($queries[$slug])) {
                $this->error("Slug '{$slug}' tidak ditemukan di config pexels.");

                return self::FAILURE;
            }
            $target = [$slug => $queries[$slug]];
        } else {
            $target = $queries;
        }

        $this->info('Memulai unduh gambar Pexels untuk ' . count($target) . ' template...');
        $bar = $this->output->createProgressBar(count($target));
        $bar->start();

        $generated = 0;
        $failed = [];

        foreach ($target as $templateSlug => $query) {
            $bar->advance();

            if ($regenerate) {
                $this->wipe($templateSlug);
            }

            try {
                $result = $service->generateForTemplate($templateSlug);

                if (($result['status'] ?? '') !== 'success' || empty($result['variants'])) {
                    $failed[] = $templateSlug;
                    continue;
                }

                // Update DB thumbnail dengan URL statis thumb
                $thumbUrl = $result['variants']['thumb'] ?? null;
                if ($thumbUrl && ($template = Template::where('slug', $templateSlug)->first())) {
                    $template->thumbnail = $thumbUrl;
                    $template->save();
                }

                $generated++;
            } catch (\Throwable $e) {
                $this->error("\nError generating {$templateSlug}: {$e->getMessage()}");
                $failed[] = $templateSlug;
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
     * Hapus file gambar Pexels lama untuk slug tertentu (tidak menghapus preview screenshot).
     */
    protected function wipe(string $slug): void
    {
        $dir = storage_path('app/public/images/templates/' . $slug);

        if (is_dir($dir)) {
            foreach (['thumb.jpg', 'hero.jpg', 'full.jpg'] as $file) {
                @unlink($dir . '/' . $file);
            }
            foreach (glob($dir . '/gallery-*.jpg') ?: [] as $file) {
                @unlink($file);
            }
        }
    }
}
