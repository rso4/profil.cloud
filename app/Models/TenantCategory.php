<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantCategory extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'name', 'slug', 'description'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(TenantPost::class, 'category_id');
    }
}
