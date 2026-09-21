<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'title', 'slug', 'content', 'cover_image',
        'show_in_nav', 'is_published', 'sort_order',
    ];

    protected $casts = [
        'show_in_nav' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}