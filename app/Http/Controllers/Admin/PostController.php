<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePostRequest;
use App\Models\Tenant;
use App\Models\TenantPost;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * CRUD Artikel (blog) milik tenant — inti CMS panel admin.
 *
 * Mendukung draf/terbit/terjadwal, soft delete (sampah + pulihkan),
 * kategori & tag, cover dari media library, dan field SEO.
 */
class PostController extends Controller
{
    use AuthorizesTenant;

    public function index(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $status = $request->query('status');
        $q = trim((string) $request->query('q'));

        $posts = $tenant->posts()
            ->with(['category', 'tags', 'cover'])
            ->when($status === TenantPost::STATUS_PUBLISHED, fn ($query) => $query->where('status', TenantPost::STATUS_PUBLISHED))
            ->when($status === TenantPost::STATUS_SCHEDULED, fn ($query) => $query->where('status', TenantPost::STATUS_SCHEDULED))
            ->when($status === TenantPost::STATUS_DRAFT, fn ($query) => $query->where('status', TenantPost::STATUS_DRAFT))
            ->when($q !== '', fn ($query) => $query->where('title', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'all' => $tenant->posts()->count(),
            'published' => $tenant->posts()->where('status', TenantPost::STATUS_PUBLISHED)->count(),
            'scheduled' => $tenant->posts()->where('status', TenantPost::STATUS_SCHEDULED)->count(),
            'draft' => $tenant->posts()->where('status', TenantPost::STATUS_DRAFT)->count(),
            'trash' => $tenant->posts()->onlyTrashed()->count(),
        ];

        return view('admin.posts', compact('tenant', 'posts', 'counts', 'status', 'q'));
    }

    public function create(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();
        $categories = $tenant->categories()->orderBy('name')->get();

        return view('admin.post-form', ['post' => null] + compact('tenant', 'categories'));
    }

    public function store(SavePostRequest $request)
    {
        $tenant = current_tenant();

        $post = new TenantPost;
        $post->tenant_id = $tenant->id;
        $post->author_id = $request->user()->id;

        $this->fillPost($post, $request, $tenant);
        $post->save();

        $this->syncTags($tenant, $post, (string) $request->input('tags'));

        return redirect()
            ->route('tenant.posts.edit', $post->id)
            ->with('success', "Artikel '{$post->title}' berhasil dibuat.");
    }

    public function edit(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $post = $tenant->posts()->with(['category', 'tags'])->findOrFail($id);
        $categories = $tenant->categories()->orderBy('name')->get();

        return view('admin.post-form', compact('tenant', 'post', 'categories'));
    }

    public function update(SavePostRequest $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $post = $tenant->posts()->findOrFail($id);

        $this->fillPost($post, $request, $tenant, true);
        $post->save();

        $this->syncTags($tenant, $post, (string) $request->input('tags'));

        return back()->with('success', "Artikel '{$post->title}' berhasil diperbarui.");
    }

    /**
     * Aksi cepat: terbitkan / kembalikan ke draf.
     */
    public function togglePublish(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();
        $post = $tenant->posts()->findOrFail($id);

        if ($post->status === TenantPost::STATUS_PUBLISHED) {
            $post->status = TenantPost::STATUS_DRAFT;
            $message = "Artikel '{$post->title}' dikembalikan ke draf.";
        } else {
            $post->status = TenantPost::STATUS_PUBLISHED;
            $post->published_at = ($post->published_at && $post->published_at->isPast())
                ? $post->published_at
                : now();
            $message = "Artikel '{$post->title}' berhasil dipublikasikan.";
        }

        $post->save();

        return back()->with('success', $message);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $post = $tenant->posts()->findOrFail($id);
        $title = $post->title;
        $post->delete(); // soft delete -> sampah

        return back()->with('success', "Artikel '{$title}' dipindahkan ke sampah.");
    }

    public function trash(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $posts = $tenant->posts()->onlyTrashed()->with(['category'])->orderByDesc('id')->paginate(10);

        return view('admin.post-trash', compact('tenant', 'posts'));
    }

    public function restore(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $post = $tenant->posts()->onlyTrashed()->findOrFail($id);
        $post->restore();

        return redirect()->route('tenant.posts')->with('success', "Artikel '{$post->title}' berhasil dipulihkan.");
    }

    public function forceDelete(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $post = $tenant->posts()->onlyTrashed()->findOrFail($id);
        $title = $post->title;
        $post->forceDelete(); // file cover milik media library, tidak ikut dihapus

        return back()->with('success', "Artikel '{$title}' dihapus permanen.");
    }

    // ===== Helper privat =====

    /**
     * Mengisi atribut artikel dari request yang sudah tervalidasi.
     */
    private function fillPost(TenantPost $post, SavePostRequest $request, Tenant $tenant, bool $isUpdate = false): void
    {
        $data = $request->validated();

        $post->title = $data['title'];

        // Slug: otomatis dari judul jika kosong, unik per tenant (termasuk sampah)
        $slug = trim($data['slug'] ?? '');
        if ($slug === '') {
            $slug = Str::slug($data['title']);
        }
        if ($slug === '') {
            $slug = 'artikel';
        }
        $post->slug = $this->uniqueSlug($tenant, $slug, $isUpdate ? $post->id : null);

        // Konten rich text disanitasi (anti-XSS)
        $post->content = sanitize_html($data['content'] ?? '');

        // Ringkasan: manual atau otomatis dari konten
        $excerpt = trim($data['excerpt'] ?? '');
        $post->excerpt = $excerpt !== '' ? $excerpt : $this->autoExcerpt($post->content);

        // Kategori & cover: hanya boleh dari tenant sendiri (isolasi lintas tenant)
        $category = ! empty($data['category_id']) ? $tenant->categories()->find($data['category_id']) : null;
        $post->category_id = $category?->id;

        $cover = ! empty($data['cover_media_id']) ? $tenant->media()->find($data['cover_media_id']) : null;
        $post->cover_media_id = $cover?->id;
        $post->cover_image = $cover?->file_path;

        // Status & jadwal tayang
        $post->status = $data['status'];
        $publishedAt = ! empty($data['published_at']) ? Carbon::parse($data['published_at']) : null;

        if ($post->status === TenantPost::STATUS_DRAFT) {
            $post->published_at = null;
        } elseif ($post->status === TenantPost::STATUS_PUBLISHED) {
            if ($publishedAt === null || $publishedAt->isFuture()) {
                $publishedAt = now();
            }
            $post->published_at = $publishedAt;
        } else { // scheduled
            $post->published_at = $publishedAt ?? now();
        }

        $post->is_featured = $request->boolean('is_featured');
        $post->meta_title = trim($data['meta_title'] ?? '') !== '' ? $data['meta_title'] : null;
        $post->meta_description = trim($data['meta_description'] ?? '') !== '' ? $data['meta_description'] : null;
    }

    private function uniqueSlug(Tenant $tenant, string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i = 1;

        while ($this->slugExists($tenant, $slug, $ignoreId)) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    private function slugExists(Tenant $tenant, string $slug, ?int $ignoreId): bool
    {
        $query = $tenant->posts()->withTrashed()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function autoExcerpt(string $content): ?string
    {
        $text = trim(strip_tags($content));

        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, 200).(mb_strlen($text) > 200 ? '...' : '');
    }

    /**
     * Sinkronisasi tag dari input "tag1, tag2, ..." (max 20).
     * Tag dibuat otomatis jika belum ada; tag yatim dibersihkan.
     */
    private function syncTags(Tenant $tenant, TenantPost $post, string $tagsInput): void
    {
        $names = collect(preg_split('/[,;]/', $tagsInput))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->take(20);

        $tagIds = $names
            ->map(function (string $name) use ($tenant) {
                $slug = Str::slug($name);

                if ($slug === '') {
                    return null;
                }

                return $tenant->tags()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name]
                )->id;
            })
            ->filter()
            ->values();

        $post->tags()->sync($tagIds);

        // Bersihkan tag yang tidak lagi dipakai artikel mana pun milik tenant
        $tenant->tags()->whereDoesntHave('posts')->delete();
    }
}
