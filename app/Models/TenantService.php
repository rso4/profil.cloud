<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantService extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'name', 'description', 'icon', 'price', 'sort_order'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
