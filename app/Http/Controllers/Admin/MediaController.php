<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Services\MediaService;
use Illuminate\Http\Request;

/**
 * Media library milik tenant: upload, daftar, alt text, hapus.
 * Digunakan oleh halaman media, cover artikel, dan editor rich text.
 */
class MediaController extends Controller
{
    use AuthorizesTenant;

    public function index(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $media = $tenant->media()->orderByDesc('id')->paginate(24);

        return view('admin.media', compact('tenant', 'media'));
    }

    /**
     * Upload media baru (endpoint AJAX dari media library / picker editor).
     */
    public function store(StoreMediaRequest $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $media = app(MediaService::class)->store(
            $tenant,
            $request->user(),
            $request->file('file'),
            $request->input('alt_text')
        );

        return response()->json([
            'id' => $media->id,
            'name' => $media->name,
            'url' => $media->url,
            'thumb' => $media->thumb_url,
            'alt' => $media->alt_text,
        ]);
    }

    /**
     * Daftar media (JSON) untuk media picker editor.
     */
    public function list(Request $request)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $query = $tenant->media()->orderByDesc('id');
        $q = trim((string) $request->query('q'));

        if ($q !== '') {
            $query->where('name', 'like', '%'.$q.'%');
        }

        return response()->json(
            $query->paginate(30)->through(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'url' => $item->url,
                'thumb' => $item->thumb_url,
                'alt' => $item->alt_text,
            ])
        );
    }

    /**
     * Perbarui alt text media (aksesibilitas & SEO).
     */
    public function update(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $data = $request->validate(['alt_text' => 'nullable|string|max:255']);
        $tenant->media()->findOrFail($id)->update($data);

        return back()->with('success', 'Alt text media berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeTenant($request);
        $tenant = current_tenant();

        $media = $tenant->media()->findOrFail($id);

        if (! app(MediaService::class)->delete($media)) {
            return back()->with('error', 'Media masih digunakan artikel/halaman dan tidak dapat dihapus.');
        }

        return back()->with('success', 'Media berhasil dihapus.');
    }
}
