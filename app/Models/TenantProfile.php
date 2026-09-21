<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'tagline', 'description', 'logo', 'cover_image', 'about_image',
        'website', 'instagram', 'facebook',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * URL gambar "Tentang" (path/URL kustom tenant).
     */
    public function getAboutImageUrlAttribute(): string
    {
        return $this->resolveImageUrl($this->about_image);
    }

    /**
     * URL gambar hero/cover kustom tenant.
     */
    public function getCoverImageUrlAttribute(): string
    {
        return $this->resolveImageUrl($this->cover_image);
    }

    /**
     * Resolusi path/URL gambar profil menjadi URL publik yang valid.
     */
    protected function resolveImageUrl(?string $value): string
    {
        if (! $value) {
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
