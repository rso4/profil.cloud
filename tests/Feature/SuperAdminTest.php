<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->superAdmin()->create();
        Template::factory()->create(['slug' => 'hotel-01']);
    }

    public function test_super_admin_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/superadmin');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_create_tenant(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/superadmin/tenants', [
            'name' => 'Test Hotel',
            'slug' => 'test-hotel',
            'email' => 'test@hotel.com',
            'phone' => '0811',
            'address' => 'Jl Test',
            'template_slug' => 'hotel-01',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'admin_name' => 'Admin Test',
            'admin_email' => 'admin@test-hotel.com',
            'admin_password' => 'password123',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tenants', ['slug' => 'test-hotel']);
        $this->assertDatabaseHas('users', ['email' => 'admin@test-hotel.com', 'role' => 'tenant_admin']);
    }

    public function test_super_admin_can_toggle_tenant_status(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'hotel', 'is_active' => true]);

        $response = $this->actingAs($this->superAdmin)
            ->post("/superadmin/tenants/{$tenant->id}/toggle");

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'is_active' => false]);
    }

    public function test_super_admin_can_delete_tenant(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'hotel']);

        $response = $this->actingAs($this->superAdmin)
            ->delete("/superadmin/tenants/{$tenant->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
    }

    public function test_tenant_creation_validates_unique_slug(): void
    {
        Tenant::factory()->create(['slug' => 'hotel']);

        $response = $this->actingAs($this->superAdmin)->post('/superadmin/tenants', [
            'name' => 'Duplicate',
            'slug' => 'hotel',
            'template_slug' => 'hotel-01',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'admin_name' => 'Admin',
            'admin_email' => 'admin@dup.com',
            'admin_password' => 'password123',
        ]);

        $response->assertSessionHasErrors('slug');
    }
}
