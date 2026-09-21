<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TenantTag extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'name', 'slug'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(TenantPost::class, 'tenant_post_tag', 'tag_id', 'post_id');
    }
}
