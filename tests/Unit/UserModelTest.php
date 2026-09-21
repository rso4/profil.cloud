<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_role_detection(): void
    {
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => null]);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertFalse($user->isTenantAdmin());
    }

    public function test_tenant_admin_role_detection(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => 'tenant_admin', 'tenant_id' => $tenant->id]);

        $this->assertTrue($user->isTenantAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_user_belongs_to_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertInstanceOf(Tenant::class, $user->tenant);
        $this->assertEquals($tenant->id, $user->tenant->id);
    }
}
