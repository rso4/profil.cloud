<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TenantCategory>
 */
class TenantCategoryFactory extends Factory
{
    protected $model = TenantCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
        ];
    }
}
