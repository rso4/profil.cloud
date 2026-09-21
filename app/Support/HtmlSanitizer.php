<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitizer HTML berbasis whitelist untuk konten CMS.
 *
 * Melindungi website tenant dari XSS dengan:
 * - Hanya tag & atribut pada whitelist yang dipertahankan.
 * - Semua atribut event (on*) dan style dibuang.
 * - URL divalidasi: protokol javascript:/data:/vbscript: diblokir.
 * - Tag berbahaya (script, iframe, form, dll.) dihapus beserta isinya.
 */
class HtmlSanitizer
{
    /** Tag yang diizinkan beserta atributnya. */
    private const ALLOWED_TAGS = [
        'p' => [], 'br' => [], 'hr' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'h2' => [], 'h3' => [], 'h4' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'blockquote' => [], 'figure' => [], 'figcaption' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [],
        'a' => ['href', 'title'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
    ];

    /** Tag berbahaya: dibuang beserta seluruh isinya. */
    private const DROP_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input',
        'button', 'select', 'textarea', 'link', 'meta', 'title',
        'svg', 'math', 'noscript', 'template', 'frame', 'frameset',
    ];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><body>'.$html.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);

        if (! $body instanceof DOMElement) {
            return '';
        }

        self::walk($body);

        $clean = '';
        foreach (iterator_to_array($body->childNodes) as $child) {
            $clean .= $dom->saveHTML($child);
        }

        return trim($clean);
    }

    /**
     * Menelusuri node secara rekursif: buang tag berbahaya,
     * filter atribut, dan unwrap tag non-whitelist.
     */
    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMComment) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            self::filterAttributes($child, $tag);
            self::walk($child);

            if (! array_key_exists($tag, self::ALLOWED_TAGS)) {
                // Unwrap: angkat isi tag ke parent lalu buang pembungkusnya
                while ($child->firstChild !== null) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
            }
        }
    }

    /**
     * Filter atribut node sesuai whitelist + validasi URL.
     */
    private static function filterAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_TAGS[$tag] ?? [];

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);

            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! self::isAllowedUrl($attribute->value, $tag)) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if (in_array($name, ['width', 'height'], true) && ! ctype_digit($attribute->value)) {
                $element->removeAttribute($attribute->name);
            }
        }

        // Link eksternal dipaksa tab baru dengan rel aman
        if ($tag === 'a' && $element->getAttribute('href') !== ''
            && preg_match('#^https?://#i', $element->getAttribute('href'))) {
            $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'noopener noreferrer');
        }

        // Lazy-load gambar konten untuk hemat bandwidth
        if ($tag === 'img' && $element->getAttribute('loading') === '') {
            $element->setAttribute('loading', 'lazy');
        }
    }

    /**
     * Validasi URL: hanya http(s), mailto, tel, dan path relatif internal.
     */
    private static function isAllowedUrl(string $url, string $tag): bool
    {
        $url = trim($url);

        if ($url === '') {
            return false;
        }

        if ($tag === 'a' && in_array($url[0], ['#', '/'], true)) {
            return true;
        }

        if (str_starts_with($url, 'storage/')) {
            return true;
        }

        if (preg_match('#^https?://#i', $url)) {
            return true;
        }

        if ($tag === 'a' && (preg_match('#^mailto:#i', $url) || preg_match('#^tel:#i', $url))) {
            return true;
        }

        return false; // javascript:, data:, vbscript:, dsb. diblokir
    }
}
