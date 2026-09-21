<?php

use App\Models\Tenant;
use App\Support\HtmlSanitizer;

if (! function_exists('current_tenant')) {
    /**
     * Mengambil tenant aktif dari container aplikasi.
     */
    function current_tenant(): ?Tenant
    {
        return app()->bound('current_tenant') ? app('current_tenant') : null;
    }
}

if (! function_exists('hex_to_rgb')) {
    /**
     * Mengubah kode HEX warna menjadi string "r,g,b".
     */
    function hex_to_rgb(?string $hex, string $fallback = '37,99,235'): string
    {
        $hex = ltrim($hex ?? '', '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return $fallback;
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r},{$g},{$b}";
    }
}

if (! function_exists('static_image')) {
    /**
     * Mengembalikan path publik gambar statis jika tersedia, fallback ke URL dinamis.
     *
     * Peta slug => path relatif terhadap public/ (lihat config template_images).
     * Jika file ada di storage/app/public/images/templates/{slug}/{key}.jpg, pakai asset().
     * Jika tidak, fallback ke URL dinamis (text_to_image).
     *
     * @param  string  $slug  Slug template (mis. hotel-01)
     * @param  string  $key  Salah satu: 'thumb' | 'hero' | 'full'
     * @param  string  $fallbackPrompt  Prompt text_to_image jika file statis belum ada
     * @param  string  $aspect  Aspek text_to_image (landscape_4_3 / landscape_16_9 / square)
     */
    function static_image(string $slug, string $key = 'thumb', string $fallbackPrompt = '', string $aspect = 'landscape_4_3'): string
    {
        $relativePath = "storage/images/templates/{$slug}/{$key}.jpg";
        $absolutePath = public_path($relativePath);

        if (file_exists($absolutePath)) {
            return asset($relativePath);
        }

        // Fallback ke URL dinamis jika file statis belum di-generate
        $base = config('images.dynamic_url', 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image');

        return $base.'?image_size='.$aspect.'&prompt='.urlencode($fallbackPrompt);
    }
}

if (! function_exists('sanitize_html')) {
    /**
     * Sanitasi HTML konten CMS berbasis whitelist (anti-XSS).
     * Hanya tag/atribut yang diizinkan yang dipertahankan;
     * atribut event, style, dan protokol URL berbahaya dibuang.
     */
    function sanitize_html(?string $html): string
    {
        return HtmlSanitizer::clean($html);
    }
}

if (! function_exists('rich_content')) {
    /**
     * Merender konten CMS dengan aman:
     * - Konten rich text (mengandung tag yang diizinkan) disanitasi ulang lalu dirender.
     * - Konten teks polos (data lama / tanpa tag) di-escape + nl2br.
     */
    function rich_content(?string $content): string
    {
        $content = trim((string) $content);

        if ($content === '') {
            return '';
        }

        if (preg_match('/<(p|h[2-4]|ul|ol|li|blockquote|strong|b|em|i|u|a|img|table|figure|figcaption|hr)\b/i', $content)) {
            return sanitize_html($content);
        }

        return nl2br(e($content));
    }
}
