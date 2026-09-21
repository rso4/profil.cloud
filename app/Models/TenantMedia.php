<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'uploaded_by', 'name', 'file_path', 'thumb_path',
        'mime_type', 'size', 'alt_text',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * URL publik file asli.
     */
    public function getUrlAttribute(): string
    {
        return $this->resolveUrl($this->file_path);
    }

    /**
     * URL thumbnail (fallback ke file asli jika thumbnail tidak tersedia).
     */
    public function getThumbUrlAttribute(): string
    {
        return $this->thumb_path ? $this->resolveUrl($this->thumb_path) : $this->url;
    }

    /**
     * Cek apakah media masih digunakan oleh artikel (cover/inline)
     * atau konten halaman milik tenant. Dipakai untuk mencegah
     * penghapusan file yang masih direferensikan.
     */
    public function isInUse(): bool
    {
        $needle = '%'.$this->file_path.'%';

        $postUsed = TenantPost::withTrashed()
            ->where('tenant_id', $this->tenant_id)
            ->where(function ($query) use ($needle) {
                $query->where('cover_media_id', $this->id)
                    ->orWhere('content', 'like', $needle)
                    ->orWhere('cover_image', 'like', $needle);
            })
            ->exists();

        if ($postUsed) {
            return true;
        }

        return TenantPage::where('tenant_id', $this->tenant_id)
            ->where('content', 'like', $needle)
            ->exists();
    }

    /**
     * Resolusi nilai path menjadi URL publik yang valid.
     */
    protected function resolveUrl(?string $value): string
    {
        if (!$value) {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $value) || str_starts_with($value, 'data:')) {
            return $value;
        }

        if (str_starts_with($value, 'storage/')) {
            return asset($value);
        }

        return asset('storage/'.$value);
    }
}
