<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $hotel;
    private Tenant $umkm;
    private User $hotelAdmin;
    private User $umkmAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $template = Template::factory()->create(['slug' => 'hotel-01']);

        $this->hotel = Tenant::factory()->create(['slug' => 'hotel', 'template_slug' => 'hotel-01']);
        $this->umkm = Tenant::factory()->create(['slug' => 'umkm', 'template_slug' => 'hotel-01']);

        $this->hotel->services()->create(['name' => 'Hotel Service']);
        $this->umkm->services()->create(['name' => 'UMKM Service']);

        $this->hotelAdmin = User::factory()->tenantAdmin($this->hotel->id)->create();
        $this->umkmAdmin = User::factory()->tenantAdmin($this->umkm->id)->create();
    }

    public function test_tenant_admin_can_access_own_dashboard(): void
    {
        $response = $this->actingAs($this->hotelAdmin)
            ->get('http://hotel.profil.cloud/admin');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee($this->hotel->name);
    }

    public function test_tenant_admin_cannot_access_other_tenant_dashboard(): void
    {
        // Hotel admin mencoba akses dashboard UMKM
        $response = $this->actingAs($this->hotelAdmin)
            ->get('http://umkm.profil.cloud/admin');

        // Harus ditolak (403) karena tenant_id tidak cocok
        $response->assertStatus(403);
    }

    public function test_tenant_admin_cannot_see_other_tenant_services(): void
    {
        // Hotel admin mengakses halaman services di subdomain UMKM
        $response = $this->actingAs($this->hotelAdmin)
            ->get('http://umkm.profil.cloud/admin/services');

        $response->assertStatus(403);
    }

    public function test_tenant_admin_cannot_modify_other_tenant_service(): void
    {
        $umkmService = $this->umkm->services()->first();

        // Hotel admin mencoba menghapus layanan milik UMKM
        $response = $this->actingAs($this->hotelAdmin)
            ->delete("http://umkm.profil.cloud/admin/services/{$umkmService->id}");

        $response->assertStatus(403);

        // Pastikan layanan UMKM masih ada
        $this->assertDatabaseHas('tenant_services', ['id' => $umkmService->id]);
    }

    public function test_super_admin_cannot_access_tenant_admin_panel(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->get('http://hotel.profil.cloud/admin');

        $response->assertStatus(403);
    }

    public function test_tenant_admin_cannot_access_super_admin_panel(): void
    {
        $response = $this->actingAs($this->hotelAdmin)
            ->get('/superadmin');

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('http://hotel.profil.cloud/admin');

        $response->assertRedirect(route('login'));
    }
}
