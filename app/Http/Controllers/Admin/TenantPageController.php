<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * CRUD Halaman Custom milik tenant.
 *
 * Admin tenant dapat menambah/mengelola halaman konten sendiri
 * yang otomatis dirender menggunakan layout template aktif,
 * termasuk integrasi ke navigasi website.
 */
class TenantPageController extends Controller
{
    /**
     * Memastikan pengguna hanya mengakses data tenant miliknya.
     */
    private function authorizeTenant(Request $request): void
    {
        $tenant = current_tenant();
        if (! $tenant || $request->user()->tenant_id !== $tenant->id) {
            abort(403, 'Akses lintas tenant ditolak.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();
        $pages = $tenant->pages()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.pages', compact('tenant', 'pages'));
    }

    public function store(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/',
            'content' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'show_in_nav' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Slug otomatis dari judul jika tidak diisi; unik per tenant.
        $slug = $data['slug'] ?? Str::slug($data['title']);
        $baseSlug = $slug;
        $i = 1;
        while ($tenant->pages()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.(++$i);
        }

        $page = new TenantPage([
            'title' => $data['title'],
            'slug' => $slug,
            // Sanitasi HTML (whitelist) — perlindungan XSS bagi konten rich text
            'content' => isset($data['content']) ? sanitize_html($data['content']) : null,
            'show_in_nav' => $request->boolean('show_in_nav', true),
            'is_published' => $request->boolean('is_published', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('pages/'.$tenant->id, 'public');
            $page->cover_image = 'storage/'.$path;
        }

        $tenant->pages()->save($page);

        return back()->with('success', "Halaman '{$page->title}' berhasil dibuat.");
    }

    public function update(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        // Isolasi: hanya halaman milik tenant ini
        $page = $tenant->pages()->findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/',
            'content' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'show_in_nav' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Slug unik per tenant (abaikan halaman ini sendiri)
        $slug = $data['slug'] ?? $page->slug;
        $exists = $tenant->pages()
            ->where('slug', $slug)
            ->where('id', '!=', $page->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['slug' => 'Slug sudah digunakan halaman lain.'])->withInput();
        }

        $page->fill([
            'title' => $data['title'],
            'slug' => $slug,
            // Sanitasi HTML (whitelist) — perlindungan XSS bagi konten rich text
            'content' => isset($data['content']) ? sanitize_html($data['content']) : $page->content,
            'show_in_nav' => $request->boolean('show_in_nav'),
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $data['sort_order'] ?? $page->sort_order,
        ]);

        if ($request->hasFile('cover_image')) {
            // Hapus cover lama jika milik disk public
            if ($page->cover_image && str_starts_with($page->cover_image, 'storage/')) {
                Storage::disk('public')->delete(substr($page->cover_image, strlen('storage/')));
            }
            $path = $request->file('cover_image')->store('pages/'.$tenant->id, 'public');
            $page->cover_image = 'storage/'.$path;
        }

        $page->save();

        return back()->with('success', "Halaman '{$page->title}' berhasil diperbarui.");
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $page = $tenant->pages()->findOrFail($id);

        if ($page->cover_image && str_starts_with($page->cover_image, 'storage/')) {
            Storage::disk('public')->delete(substr($page->cover_image, strlen('storage/')));
        }

        $page->delete();

        return back()->with('success', 'Halaman berhasil dihapus.');
    }
}
