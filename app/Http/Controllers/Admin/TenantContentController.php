<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantContentController extends Controller
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

    // ===== Profil =====
    public function editProfile(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();
        $profile = $tenant->profile ?? $tenant->profile()->create([]);

        return view('admin.profile', compact('tenant', 'profile'));
    }

    public function updateProfile(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'about_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        // Nama bisnis tersimpan di tabel tenants (tampil di navbar/hero/footer
        // template) — bukan kolom tenant_profiles, keluarkan dari $data.
        $tenant->name = $data['name'];
        $tenant->save();
        unset($data['name']);

        // Upload gambar hero/beranda (ganti = hapus file lama milik tenant)
        $coverPath = null;
        $aboutPath = null;

        if ($request->hasFile('cover_image')) {
            $this->deleteProfileImage($tenant->profile?->cover_image);
            $coverPath = 'storage/'.$request->file('cover_image')->store('profiles/'.$tenant->id, 'public');
        }

        // Upload gambar section "Tentang"
        if ($request->hasFile('about_image')) {
            $this->deleteProfileImage($tenant->profile?->about_image);
            $aboutPath = 'storage/'.$request->file('about_image')->store('profiles/'.$tenant->id, 'public');
        }

        $tenant->profile()->updateOrCreate(['tenant_id' => $tenant->id], $data);

        // Simpan path gambar setelah record profil dipastikan ada
        if ($coverPath !== null || $aboutPath !== null) {
            $profile = $tenant->profile()->first();
            $profile->cover_image = $coverPath ?? $profile->cover_image;
            $profile->about_image = $aboutPath ?? $profile->about_image;
            $profile->save();
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Hapus file fisik gambar profil milik tenant (disk public).
     */
    private function deleteProfileImage(?string $value): void
    {
        if ($value && str_starts_with($value, 'storage/')) {
            Storage::disk('public')->delete(substr($value, strlen('storage/')));
        }
    }

    // ===== Tema =====
    public function editTheme(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        return view('admin.theme', compact('tenant'));
    }

    public function updateTheme(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $data = $request->validate([
            'primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tenant->update($data);

        return back()->with('success', 'Tema berhasil diperbarui.');
    }

    // ===== Layanan (CRUD) =====
    public function index(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();
        $services = $tenant->services()->orderBy('sort_order')->get();

        return view('admin.services', compact('tenant', 'services'));
    }

    public function store(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
        ]);

        $tenant->services()->create($data);

        return back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        // Isolasi: hanya layanan milik tenant ini
        $service = $tenant->services()->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
        ]);

        $service->update($data);

        return back()->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $tenant->services()->findOrFail($id)->delete();

        return back()->with('success', 'Layanan berhasil dihapus.');
    }

    // ===== Galeri (CRUD) =====
    public function galleries(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();
        $galleries = $tenant->galleries()->orderBy('sort_order')->get();

        return view('admin.galleries', compact('tenant', 'galleries'));
    }

    public function storeGallery(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        // Rule "url" tidak boleh digabung dengan rule implicit (required_without/
        // required) dalam satu set: saat upload file, field image bernilai null
        // dan rule url tetap dievaluasi -> error "must be a valid URL".
        $rules = [
            'title' => 'nullable|string|max:255',
            'image' => ['nullable', 'url'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
        ];

        // Wajib ada salah satu: file upload ATAU URL gambar
        if (! $request->hasFile('image_file')) {
            $rules['image'] = 'required|url';
        }

        $data = $request->validate($rules);

        // Prioritas: file upload > URL eksternal
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('galleries/'.$tenant->id, 'public');
            $data['image'] = 'storage/'.$path;
            $data['thumb'] = 'storage/'.$this->makeGalleryThumbnail($path);
        } elseif (! preg_match('/^https?:\/\//i', $data['image']) && ! str_starts_with($data['image'], 'data:')) {
            // Bukan URL absolut -> simpan sebagai path relatif di disk public
            $data['image'] = 'storage/'.ltrim($data['image'], '/');
        }

        $tenant->galleries()->create($data);

        return back()->with('success', 'Gambar berhasil ditambahkan.');
    }

    /**
     * Buat thumbnail ringan dari gambar galeri yang diupload
     * menggunakan intervention/image v3 (via ImageGenerationService).
     *
     * @param  string  $sourcePath  Path relatif file asli pada disk public
     * @return string Path relatif thumbnail pada disk public
     */
    private function makeGalleryThumbnail(string $sourcePath): string
    {
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
        $targetPath = preg_replace('/\.'.preg_quote($extension, '/').'$/', '', $sourcePath)
            .'-thumb.'.$extension;

        $created = app(ImageGenerationService::class)
            ->createThumbnail($sourcePath, $targetPath);

        return $created ? $targetPath : $sourcePath;
    }

    public function updateGallery(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        // Isolasi: hanya gambar milik tenant ini
        $gallery = $tenant->galleries()->findOrFail($id);

        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|url',
            'image_file' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        // Prioritas: file upload baru > URL baru > pertahankan gambar lama
        if ($request->hasFile('image_file')) {
            // Hapus file fisik lama (gambar + thumbnail) milik tenant ini saja
            foreach (['image', 'thumb'] as $field) {
                $relativePath = $gallery->{$field} ?? null;

                if ($relativePath && str_starts_with($relativePath, 'storage/')) {
                    Storage::disk('public')->delete(substr($relativePath, strlen('storage/')));
                }
            }

            $path = $request->file('image_file')->store('galleries/'.$tenant->id, 'public');
            $gallery->image = 'storage/'.$path;
            $gallery->thumb = 'storage/'.$this->makeGalleryThumbnail($path);
        } elseif (! empty($data['image'])) {
            $gallery->image = preg_match('/^https?:\/\//i', $data['image']) || str_starts_with($data['image'], 'data:')
                ? $data['image']
                : 'storage/'.ltrim($data['image'], '/');
            // Thumbnail lama tidak lagi cocok dengan gambar baru
            $gallery->thumb = null;
        }

        if (array_key_exists('title', $data)) {
            $gallery->title = $data['title'];
        }

        $gallery->save();

        return back()->with('success', 'Gambar berhasil diperbarui.');
    }

    public function destroyGallery(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $gallery = $tenant->galleries()->findOrFail($id);

        // Hapus file fisik (gambar asli + thumbnail) milik tenant ini saja.
        foreach (['image', 'thumb'] as $field) {
            $relativePath = $gallery->{$field} ?? null;

            if ($relativePath && str_starts_with($relativePath, 'storage/')) {
                Storage::disk('public')->delete(substr($relativePath, strlen('storage/')));
            }
        }

        $gallery->delete();

        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    // ===== Kontak (CRUD) =====
    public function contacts(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();
        $contacts = $tenant->contacts()->orderBy('sort_order')->get();

        return view('admin.contacts', compact('tenant', 'contacts'));
    }

    public function storeContact(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $data = $request->validate([
            'label' => 'nullable|string|max:255',
            'value' => 'required|string|max:255',
            'type' => 'required|in:phone,email,address,social',
        ]);

        $tenant->contacts()->create($data);

        return back()->with('success', 'Kontak berhasil ditambahkan.');
    }

    public function editContact(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        // Isolasi: hanya kontak milik tenant ini
        $contact = $tenant->contacts()->findOrFail($id);

        return view('admin.contact-edit', compact('tenant', 'contact'));
    }

    public function updateContact(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        // Isolasi: hanya kontak milik tenant ini
        $contact = $tenant->contacts()->findOrFail($id);

        $data = $request->validate([
            'label' => 'nullable|string|max:255',
            'value' => 'required|string|max:255',
            'type' => 'required|in:phone,email,address,social',
        ]);

        $contact->update($data);

        return back()->with('success', 'Kontak berhasil diperbarui.');
    }

    public function destroyContact(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $tenant->contacts()->findOrFail($id)->delete();

        return back()->with('success', 'Kontak berhasil dihapus.');
    }
}
