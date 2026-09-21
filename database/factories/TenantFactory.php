<?php

namespace Database\Factories;

use App\Models\Template;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $template = Template::firstOrCreate(
            ['slug' => 'hotel-01'],
            ['name' => 'Hotel Modern', 'category' => 'hotel']
        );

        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->company(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'template_slug' => $template->slug,
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
