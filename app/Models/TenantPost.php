<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantPost extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_SCHEDULED = 'scheduled';

    protected $fillable = [
        'tenant_id', 'author_id', 'category_id', 'cover_media_id',
        'title', 'slug', 'excerpt', 'content', 'cover_image',
        'status', 'published_at', 'is_featured', 'views',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TenantCategory::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(TenantMedia::class, 'cover_media_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TenantTag::class, 'tenant_post_tag', 'post_id', 'tag_id');
    }

    /**
     * Scope: hanya artikel yang tampil di website publik
     * (status terbit/terjadwal dan waktu tayang sudah tercapai).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_SCHEDULED])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * URL cover artikel (fallback: string kosong).
     */
    public function getCoverUrlAttribute(): string
    {
        $value = $this->cover_image;

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

    /**
     * Ringkasan artikel: pakai excerpt manual, atau otomatis dari konten.
     */
    public function getExcerptTextAttribute(): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        $text = trim(strip_tags((string) $this->content));
        if ($text === '') {
            return '';
        }

        return mb_substr($text, 0, 160).(mb_strlen($text) > 160 ? '...' : '');
    }

    /**
     * Estimasi waktu baca (menit) berdasarkan jumlah kata.
     */
    public function getReadingMinutesAttribute(): int
    {
        $words = str_word_count(trim(strip_tags((string) $this->content)));

        return max(1, (int) ceil($words / 200));
    }

    /**
     * Label status untuk ditampilkan di panel admin.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'Terbit',
            self::STATUS_SCHEDULED => ($this->published_at && $this->published_at->isFuture()) ? 'Terjadwal' : 'Terbit',
            default => 'Draf',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this->status, [self::STATUS_PUBLISHED, self::STATUS_SCHEDULED], true)
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }
}
