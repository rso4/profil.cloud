<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Upload & pengelolaan media library per tenant.
 *
 * File disimpan pada disk public dengan nama acak (hashName),
 * thumbnail ringan digenerate via intervention/image.
 */
class MediaService
{
    public function store(Tenant $tenant, ?User $uploader, UploadedFile $file, ?string $alt = null): TenantMedia
    {
        $path = $file->store('media/'.$tenant->id, 'public');

        $media = $tenant->media()->create([
            'uploaded_by' => $uploader?->id,
            'name' => $file->getClientOriginalName() ?: basename($path),
            'file_path' => 'storage/'.$path,
            'mime_type' => $file->getMimeType() ?: 'image/jpeg',
            'size' => $file->getSize() ?: 0,
            'alt_text' => $alt,
        ]);

        $thumbPath = $this->makeThumbnail($path);

        if ($thumbPath !== null) {
            $media->thumb_path = 'storage/'.$thumbPath;
            $media->save();
        }

        return $media;
    }

    /**
     * Hapus media + file fisiknya. Mengembalikan false jika
     * media masih direferensikan artikel/halaman.
     */
    public function delete(TenantMedia $media): bool
    {
        if ($media->isInUse()) {
            return false;
        }

        foreach (['file_path', 'thumb_path'] as $field) {
            $relative = $media->{$field};

            if ($relative && str_starts_with($relative, 'storage/')) {
                Storage::disk('public')->delete(substr($relative, strlen('storage/')));
            }
        }

        return (bool) $media->delete();
    }

    /**
     * Buat thumbnail ringan dari gambar yang diupload.
     *
     * @param  string  $sourcePath  Path relatif file asli pada disk public
     * @return string|null Path relatif thumbnail, null jika gagal
     */
    private function makeThumbnail(string $sourcePath): ?string
    {
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
        $targetPath = preg_replace('/\.'.preg_quote($extension, '/').'$/', '', $sourcePath)
            .'-thumb.'.$extension;

        $created = app(ImageGenerationService::class)->createThumbnail($sourcePath, $targetPath);

        return $created ? $targetPath : null;
    }
}
