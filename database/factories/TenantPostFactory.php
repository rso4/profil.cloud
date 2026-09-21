<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantPost>
 */
class TenantPostFactory extends Factory
{
    protected $model = TenantPost::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'author_id' => null,
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(2),
            'excerpt' => fake()->paragraph(),
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'status' => TenantPost::STATUS_PUBLISHED,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'is_featured' => false,
            'views' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => TenantPost::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => TenantPost::STATUS_SCHEDULED,
            'published_at' => now()->addDays(2),
        ]);
    }
}
