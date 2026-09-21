<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Merender preview template website dengan data dummy agar dapat
 * di-screenshot menjadi thumbnail/fullscreen yang menampilkan tampilan
 * asli template (semua komponen, warna, dan CSS).
 */
class TemplatePreviewController extends Controller
{
    /**
     * Render template tertentu dengan data dummy lengkap.
     */
    public function show(string $slug): View
    {
        $template = Template::where('slug', $slug)->firstOrFail();

        $view = "templates.{$slug}.home";

        if (!view()->exists($view)) {
            abort(404, 'Template tidak ditemukan.');
        }

        $tenant = $this->dummyTenant($slug);
        $profile = $this->dummyProfile($slug);
        $services = $this->dummyServices($slug);
        $galleries = $this->dummyGalleries($slug);
        $contacts = $this->dummyContacts();

        return view($view, compact('tenant', 'profile', 'services', 'galleries', 'contacts'));
    }

    /**
     * Data tenant dummy dengan warna tematik per kategori.
     */
    protected function dummyTenant(string $slug): object
    {
        $category = str_starts_with($slug, 'hotel') ? 'hotel'
            : (str_starts_with($slug, 'school') ? 'school' : 'sme');

        $colors = match ($category) {
            'hotel' => ['primary' => '#2563eb', 'secondary' => '#0f172a'],
            'school' => ['primary' => '#059669', 'secondary' => '#064e3b'],
            default => ['primary' => '#ea580c', 'secondary' => '#431407'],
        };

        $name = ucwords(str_replace(['-', '_'], ' ', $slug));

        return (object) [
            'id' => 0,
            'slug' => $slug,
            'name' => $name,
            'template_slug' => $slug,
            'primary_color' => $colors['primary'],
            'secondary_color' => $colors['secondary'],
            'is_active' => true,
        ];
    }

    /**
     * Profil dummy dengan tagline dan deskripsi tematik.
     */
    protected function dummyProfile(string $slug): object
    {
        $category = str_starts_with($slug, 'hotel') ? 'hotel'
            : (str_starts_with($slug, 'school') ? 'school' : 'sme');

        $tagline = match ($category) {
            'hotel' => 'Pengalaman menginap terbaik untuk Anda',
            'school' => 'Mendidik generasi masa depan',
            default => 'Produk dan layanan berkualitas untuk Anda',
        };

        $description = match ($category) {
            'hotel' => 'Kami menyediakan akomodasi nyaman dengan fasilitas lengkap, pelayanan ramah, dan lokasi strategis untuk kebutuhan bisnis maupun liburan Anda.',
            'school' => 'Lembaga pendidikan yang berkomitmen mencetak generasi unggul melalui pembelajaran berkualitas, tenaga pendidik profesional, dan lingkungan yang mendukung.',
            default => 'Kami hadir untuk memenuhi kebutuhan Anda dengan produk dan layanan terbaik, didukung tim profesional dan komitmen terhadap kepuasan pelanggan.',
        };

        return (object) [
            'tagline' => $tagline,
            'description' => $description,
            'logo' => null,
            'cover_image' => null,
            'cover_image_url' => null,
            'about_image_url' => null,
            'website' => 'https://example.com',
            'instagram' => '@profilcloud',
            'facebook' => 'Profil Cloud',
        ];
    }

    /**
     * Layanan dummy (3 item) dengan ikon dan harga tematik.
     */
    protected function dummyServices(string $slug): Collection
    {
        $category = str_starts_with($slug, 'hotel') ? 'hotel'
            : (str_starts_with($slug, 'school') ? 'school' : 'sme');

        $sets = match ($category) {
            'hotel' => [
                ['name' => 'Kamar Deluxe', 'description' => 'Kamar luas dengan pemandangan kota dan fasilitas premium.', 'icon' => '🛏️', 'price' => 1500000],
                ['name' => 'Restoran', 'description' => 'Menu internasional dan lokal dengan bahan segar pilihan.', 'icon' => '🍽️', 'price' => 250000],
                ['name' => 'Kolam Renang', 'description' => 'Kolam renang rooftop dengan pemandangan panorama.', 'icon' => '🏊', 'price' => 100000],
            ],
            'school' => [
                ['name' => 'Kelas Reguler', 'description' => 'Program belajar intensif dengan kurikulum terpadu.', 'icon' => '📚', 'price' => 500000],
                ['name' => 'Ekstrakurikuler', 'description' => 'Beragam kegiatan pengembangan bakat dan minat siswa.', 'icon' => '⚽', 'price' => 150000],
                ['name' => 'Konsultasi', 'description' => 'Bimbingan akademik dan konseling untuk siswa.', 'icon' => '🧑‍🏫', 'price' => 100000],
            ],
            default => [
                ['name' => 'Produk Unggulan', 'description' => 'Produk berkualitas dengan harga terjangkau.', 'icon' => '📦', 'price' => 150000],
                ['name' => 'Layanan Kustom', 'description' => 'Solusi sesuai kebutuhan spesifik pelanggan.', 'icon' => '🛠️', 'price' => 250000],
                ['name' => 'Konsultasi', 'description' => 'Konsultasi profesional untuk kebutuhan bisnis Anda.', 'icon' => '💼', 'price' => 100000],
            ],
        };

        return collect($sets)->map(fn ($s, $i) => (object) array_merge($s, ['sort_order' => $i]));
    }

    /**
     * Galeri dummy (4 item) dengan gambar statis template dari Pexels.
     */
    protected function dummyGalleries(string $slug): Collection
    {
        $titles = ['Galeri 1', 'Galeri 2', 'Galeri 3', 'Galeri 4'];

        return collect($titles)->map(function ($title, $i) use ($slug) {
            // Gunakan gambar galeri Pexels (gallery-1..N) jika tersedia,
            // fallback ke gambar full/thumb statis template.
            $galleryPath = "storage/images/templates/{$slug}/gallery-" . ($i + 1) . '.jpg';
            $fullPath = "storage/images/templates/{$slug}/full.jpg";
            $thumbPath = "storage/images/templates/{$slug}/thumb.jpg";

            $imageUrl = file_exists(public_path($galleryPath))
                ? asset($galleryPath)
                : (file_exists(public_path($fullPath)) ? asset($fullPath) : static_image($slug, 'full', 'template preview image', 'square'));

            $thumbUrl = file_exists(public_path($galleryPath))
                ? asset($galleryPath)
                : (file_exists(public_path($thumbPath)) ? asset($thumbPath) : static_image($slug, 'thumb', 'template preview image'));

            return (object) [
                'title' => $title,
                'image_url' => $imageUrl,
                'thumb_url' => $thumbUrl,
                'sort_order' => $i,
            ];
        });
    }

    /**
     * Kontak dummy dummy data.
     */
    protected function dummyContacts(): Collection
    {
        return collect([
            (object) ['label' => 'Telepon', 'value' => '+62 812 3456 7890', 'type' => 'phone', 'sort_order' => 0],
            (object) ['label' => 'Email', 'value' => 'info@example.com', 'type' => 'email', 'sort_order' => 1],
            (object) ['label' => 'Alamat', 'value' => 'Jl. Contoh No. 123, Jakarta', 'type' => 'address', 'sort_order' => 2],
        ]);
    }
}
