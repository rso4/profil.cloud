<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    /**
     * Merender website publik tenant berdasarkan template yang dipilih.
     * View engine dinamis memuat folder template sesuai slug template tenant.
     */
    public function show(Request $request): View
    {
        $tenant = current_tenant();

        if (!$tenant) {
            abort(404, 'Tenant website tidak ditemukan.');
        }

        $path = trim($request->path(), '/');

        // Blog tenant (/blog, /blog/{slug}, /blog/kategori/{slug}, /blog/tag/{slug})
        if ($path === 'blog' || str_starts_with($path, 'blog/')) {
            return $this->blog($tenant, $path);
        }

        // Halaman custom tenant yang sudah dipublikasikan
        $pages = $tenant->pages()->where('is_published', true)->orderBy('sort_order')->get();

        // Cek apakah path mengarah ke halaman custom tenant (/p/{slug})
        if (str_starts_with($path, 'p/')) {
            $slug = substr($path, 2);
            $page = $pages->firstWhere('slug', $slug);

            if (!$page) {
                abort(404, 'Halaman tidak ditemukan.');
            }

            $navPages = $pages->where('show_in_nav', true)->values();

            return view('templates.page', compact('tenant', 'page', 'navPages'));
        }

        // Muat data konten tenant (beranda).
        // Fallback model kosong jika tenant belum pernah mengisi profil —
        // template membaca $profile->cover_image_url/about_image_url langsung.
        $profile = $tenant->profile ?? new TenantProfile();
        $services = $tenant->services()->orderBy('sort_order')->get();
        $galleries = $tenant->galleries()->orderBy('sort_order')->get();
        $contacts = $tenant->contacts()->orderBy('sort_order')->get();

        // View engine dinamis: templates/{slug}/home
        $view = "templates.{$tenant->template_slug}.home";

        if (!view()->exists($view)) {
            abort(404, 'Template tidak ditemukan.');
        }

        return view($view, compact('tenant', 'profile', 'services', 'galleries', 'contacts', 'pages'));
    }

    /**
     * Blog publik tenant: daftar artikel, detail, per kategori, dan per tag.
     * Hanya artikel terbit/terjadwal yang sudah mencapai waktu tayang yang tampil.
     */
    private function blog(Tenant $tenant, string $path): View
    {
        $navPages = $tenant->pages()
            ->where('is_published', true)
            ->where('show_in_nav', true)
            ->orderBy('sort_order')
            ->get();

        // /blog/kategori/{slug}
        if (preg_match('#^blog/kategori/([a-z0-9-]+)$#', $path, $m)) {
            $category = $tenant->categories()->where('slug', $m[1])->firstOrFail();

            $posts = $tenant->posts()
                ->published()
                ->with(['category', 'cover'])
                ->where('category_id', $category->id)
                ->orderByDesc('published_at')
                ->paginate(9);

            return view('templates.blog.index', [
                'tenant' => $tenant,
                'navPages' => $navPages,
                'posts' => $posts,
                'category' => $category,
                'pageTitle' => 'Kategori: '.$category->name,
                'metaDescription' => $category->description,
            ]);
        }

        // /blog/tag/{slug}
        if (preg_match('#^blog/tag/([a-z0-9-]+)$#', $path, $m)) {
            $tag = $tenant->tags()->where('slug', $m[1])->firstOrFail();

            $posts = $tenant->posts()
                ->published()
                ->with(['category', 'cover'])
                ->whereHas('tags', fn ($query) => $query->where('tenant_tags.id', $tag->id))
                ->orderByDesc('published_at')
                ->paginate(9);

            return view('templates.blog.index', [
                'tenant' => $tenant,
                'navPages' => $navPages,
                'posts' => $posts,
                'tag' => $tag,
                'pageTitle' => 'Tag: '.$tag->name,
                'metaDescription' => 'Artikel dengan tag '.$tag->name.' dari '.$tenant->name,
            ]);
        }

        // /blog/{slug} — detail artikel
        if (preg_match('#^blog/([a-z0-9-]+)$#', $path, $m)) {
            $post = $tenant->posts()
                ->published()
                ->with(['category', 'tags', 'cover', 'author'])
                ->where('slug', $m[1])
                ->first();

            if (!$post) {
                abort(404, 'Artikel tidak ditemukan.');
            }

            // Tambah counter dilihat — atomik & tanpa menyentuh updated_at
            $post->newModelQuery()->toBase()->where('id', $post->id)->increment('views');
            $post->views += 1;

            $relatedPosts = $tenant->posts()
                ->published()
                ->when($post->category_id, fn ($query) => $query->where('category_id', $post->category_id))
                ->whereKeyNot($post->id)
                ->orderByDesc('published_at')
                ->take(3)
                ->get();

            return view('templates.blog.show', [
                'tenant' => $tenant,
                'navPages' => $navPages,
                'post' => $post,
                'relatedPosts' => $relatedPosts,
                'pageTitle' => $post->meta_title ?? $post->title,
                'metaDescription' => $post->meta_description ?? $post->excerpt_text,
            ]);
        }

        // /blog — daftar artikel (unggulan ditampilkan lebih dulu)
        if ($path !== 'blog') {
            abort(404, 'Halaman tidak ditemukan.');
        }

        $posts = $tenant->posts()
            ->published()
            ->with(['category', 'cover'])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('templates.blog.index', [
            'tenant' => $tenant,
            'navPages' => $navPages,
            'posts' => $posts,
            'pageTitle' => 'Blog',
            'metaDescription' => 'Artikel terbaru dari '.$tenant->name,
        ]);
    }
}
