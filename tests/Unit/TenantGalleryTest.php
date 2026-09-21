<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\TenantGallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantGalleryTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create(['slug' => 'hotel']);
    }

    public function test_image_url_returns_absolute_url_unchanged(): void
    {
        $gallery = TenantGallery::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Foto',
            'image' => 'https://example.com/photo.jpg',
        ]);

        $this->assertSame('https://example.com/photo.jpg', $gallery->image_url);
    }

    public function test_image_url_resolves_storage_relative_path(): void
    {
        $gallery = TenantGallery::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Foto',
            'image' => 'storage/galleries/1/photo.jpg',
        ]);

        $this->assertSame(asset('storage/galleries/1/photo.jpg'), $gallery->image_url);
    }

    public function test_image_url_resolves_bare_filename_into_public_storage(): void
    {
        $gallery = TenantGallery::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Foto',
            'image' => 'photo.jpg',
        ]);

        $this->assertSame(asset('storage/photo.jpg'), $gallery->image_url);
    }

    public function test_image_url_returns_data_uri_unchanged(): void
    {
        $gallery = TenantGallery::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Foto',
            'image' => 'data:image/png;base64,abc123',
        ]);

        $this->assertSame('data:image/png;base64,abc123', $gallery->image_url);
    }

    public function test_thumb_url_resolves_thumbnail_path(): void
    {
        $gallery = TenantGallery::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Foto',
            'image' => 'storage/galleries/1/photo.jpg',
            'thumb' => 'storage/galleries/1/photo-thumb.jpg',
        ]);

        $this->assertSame(asset('storage/galleries/1/photo-thumb.jpg'), $gallery->thumb_url);
        $this->assertSame(asset('storage/galleries/1/photo.jpg'), $gallery->image_url);
    }

    public function test_thumb_url_falls_back_to_image_url_when_thumb_missing(): void
    {
        $gallery = TenantGallery::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Foto',
            'image' => 'https://example.com/photo.jpg',
            'thumb' => null,
        ]);

        $this->assertSame($gallery->image_url, $gallery->thumb_url);
    }

    public function test_thumb_url_falls_back_for_external_url_entries(): void
    {
        $gallery = TenantGallery::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Foto',
            'image' => 'https://example.com/photo.jpg',
        ]);

        // Entri dari URL eksternal tidak punya thumbnail lokal
        $this->assertSame('https://example.com/photo.jpg', $gallery->thumb_url);
    }
}
