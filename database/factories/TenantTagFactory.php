<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TenantTag>
 */
class TenantTagFactory extends Factory
{
    protected $model = TenantTag::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
