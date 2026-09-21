<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\TenantProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantProfileTest extends TestCase
{
    use RefreshDatabase;

    private function makeProfile(array $attributes = []): TenantProfile
    {
        $tenant = Tenant::factory()->create();

        return TenantProfile::create(array_merge([
            'tenant_id' => $tenant->id,
        ], $attributes));
    }

    public function test_cover_image_url_returns_empty_string_when_null(): void
    {
        $profile = $this->makeProfile(['cover_image' => null, 'about_image' => null]);

        $this->assertSame('', $profile->cover_image_url);
        $this->assertSame('', $profile->about_image_url);
    }

    public function test_cover_image_url_resolves_storage_path(): void
    {
        $profile = $this->makeProfile(['cover_image' => 'storage/profiles/1/hero.jpg']);

        $this->assertSame(asset('storage/profiles/1/hero.jpg'), $profile->cover_image_url);
    }

    public function test_image_url_resolves_relative_path_as_storage(): void
    {
        $profile = $this->makeProfile(['about_image' => 'profiles/1/about.jpg']);

        $this->assertSame(asset('storage/profiles/1/about.jpg'), $profile->about_image_url);
    }

    public function test_image_url_keeps_absolute_url_untouched(): void
    {
        $profile = $this->makeProfile(['cover_image' => 'https://cdn.example.com/hero.jpg']);

        $this->assertSame('https://cdn.example.com/hero.jpg', $profile->cover_image_url);
    }

    public function test_image_url_keeps_data_uri_untouched(): void
    {
        $profile = $this->makeProfile(['cover_image' => 'data:image/png;base64,iVBORw0KGgo=']);

        $this->assertSame('data:image/png;base64,iVBORw0KGgo=', $profile->cover_image_url);
    }
}