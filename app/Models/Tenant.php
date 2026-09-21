<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'email', 'phone', 'address',
        'template_slug', 'primary_color', 'secondary_color', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_slug', 'slug');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(TenantProfile::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(TenantGallery::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(TenantService::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(TenantContact::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(TenantPage::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(TenantPost::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(TenantCategory::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(TenantTag::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(TenantMedia::class);
    }
}
