<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantWebsiteTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = Template::factory()->create(['slug' => 'hotel-01']);
        $this->tenant = Tenant::factory()->create([
            'slug' => 'hotel',
            'template_slug' => 'hotel-01',
            'primary_color' => '#b45309',
            'secondary_color' => '#1e293b',
        ]);
        $this->tenant->profile()->create(['tagline' => 'Test tagline']);
        $this->tenant->services()->create(['name' => 'Kamar Deluxe', 'price' => 850000]);
    }

    public function test_tenant_website_renders_for_registered_subdomain(): void
    {
        $response = $this->get('http://hotel.profil.cloud/');

        $response->assertStatus(200);
        $response->assertSee($this->tenant->name);
        $response->assertSee('Kamar Deluxe');
    }

    public function test_tenant_theme_colors_are_injected(): void
    {
        $response = $this->get('http://hotel.profil.cloud/');

        $response->assertStatus(200);
        $response->assertSee('--primary-color: #b45309', false);
        $response->assertSee('--secondary-color: #1e293b', false);
        $response->assertSee('--primary-rgb: 180,83,9', false);
    }

    public function test_unregistered_subdomain_returns_404(): void
    {
        $response = $this->get('http://nonexistent.profil.cloud/');

        $response->assertStatus(404);
    }

    public function test_inactive_tenant_returns_404(): void
    {
        $inactive = Tenant::factory()->inactive()->create(['slug' => 'inactive']);

        $response = $this->get('http://inactive.profil.cloud/');

        $response->assertStatus(404);
    }

    public function test_main_domain_returns_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Profil Cloud');
    }
}
