<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantMedia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TenantMedia>
 */
class TenantMediaFactory extends Factory
{
    protected $model = TenantMedia::class;

    public function definition(): array
    {
        $name = Str::random(20).'.jpg';

        return [
            'tenant_id' => Tenant::factory(),
            'uploaded_by' => null,
            'name' => $name,
            'file_path' => 'storage/media/'.fake()->numberBetween(1, 999).'/'.$name,
            'thumb_path' => null,
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10000, 500000),
            'alt_text' => fake()->sentence(3),
        ];
    }
}
