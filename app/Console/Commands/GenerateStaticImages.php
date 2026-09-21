<?php

namespace App\Console\Commands;

use App\Models\Template;
use App\Services\ImageGenerationService;
use Illuminate\Console\Command;

/**
 * Pre-generate gambar statis untuk semua template (atau slug tertentu).
 *
 * Usage:
 *   php artisan images:generate                  # Semua template
 *   php artisan images:generate hotel-01         # Slug tertentu
 *   php artisan images:generate --regenerate     # Hapus & regenerate
 *
 * generateForTemplate() menghasilkan 3 varian sekaligus (thumb, hero, full).
 * Jika API eksternal tidak tersedia, service otomatis membuat mockup lokal
 * yang unik per template (deterministik dari slug).
 */
class GenerateStaticImages extends Command
{
    protected $signature = 'images:generate
                            {slug? : Slug template tertentu (opsional)}
                            {--regenerate : Hapus file lama sebelum generate ulang}';

    protected $description = 'Pre-generate gambar statis (thumbnail, hero, full) untuk semua template';

    public function handle(ImageGenerationService $service): int
    {
        $slug = $this->argument('slug');
        $regenerate = $this->option('regenerate');

        $prompts = config('template_images.prompts', []);

        if ($slug !== null) {
            if (!isset($prompts[$slug])) {
                $this->error("Slug '{$slug}' tidak ditemukan di config template_images.");

                return self::FAILURE;
            }
            $target = [$slug => $prompts[$slug]];
        } else {
            $target = $prompts;
        }

        $this->info('Memulai pre-generate untuk ' . count($target) . ' template...');
        $bar = $this->output->createProgressBar(count($target));
        $bar->start();

        $generated = 0;
        $failed = [];

        foreach ($target as $templateSlug => $mainPrompt) {
            $bar->advance();

            if ($regenerate) {
                $this->wipe($templateSlug);
            }

            try {
                $result = $service->generateForTemplate($templateSlug, $mainPrompt, 'landscape_4_3');

                if (empty($result['variants'])) {
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
     * Hapus file lama untuk slug tertentu.
     */
    protected function wipe(string $slug): void
    {
        $dir = storage_path('app/public/images/templates/' . $slug);

        if (is_dir($dir)) {
            foreach (glob($dir . '/*') as $file) {
                @unlink($file);
            }
        }
    }
}
