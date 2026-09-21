<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantGallery extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'title', 'image', 'thumb', 'sort_order'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Menghasilkan URL publik yang valid untuk gambar galeri.
     * - URL absolut (http/https/data) dikembalikan apa adanya.
     * - Path relatif "storage/..." diubah menjadi URL penuh via asset().
     * - Nilai lain diperlakukan sebagai file di dalam disk public.
     */
    public function getImageUrlAttribute(): string
    {
        return $this->resolveUrl($this->image);
    }

    /**
     * URL thumbnail galeri (versi ringan untuk grid).
     * Fallback ke image_url jika thumbnail belum tersedia (mis. URL eksternal).
     */
    public function getThumbUrlAttribute(): string
    {
        return $this->thumb ? $this->resolveUrl($this->thumb) : $this->image_url;
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
