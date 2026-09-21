<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\TenantMedia;
use App\Models\TenantPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fitur CMS: media library — upload, validasi, alt text,
 * penghapusan (termasuk proteksi media terpakai), dan
 * isolasi antar tenant.
 */
class CmsMediaTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $hotel;

    private Tenant $umkm;

    private User $hotelAdmin;

    private User $umkmAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Template::factory()->create(['slug' => 'hotel-01']);

        $this->hotel = Tenant::factory()->create(['slug' => 'hotel', 'template_slug' => 'hotel-01']);
        $this->umkm = Tenant::factory()->create(['slug' => 'umkm', 'template_slug' => 'hotel-01']);

        $this->hotelAdmin = User::factory()->tenantAdmin($this->hotel->id)->create();
        $this->umkmAdmin = User::factory()->tenantAdmin($this->umkm->id)->create();
    }

    /**
     * Upload file sebagai admin hotel.
     */
    private function upload(UploadedFile $file, ?string $alt = null)
    {
        return $this->actingAs($this->hotelAdmin)
            ->post('http://hotel.profil.cloud/admin/media', [
                'file' => $file,
                'alt_text' => $alt,
            ]);
    }

    public function test_admin_can_upload_image_media(): void
    {
        Storage::fake('public');

        $this->upload(UploadedFile::fake()->image('kamar.jpg', 400, 300), 'Kamar deluxe')
            ->assertOk()
            ->assertJsonPath('name', 'kamar.jpg')
            ->assertJsonPath('alt', 'Kamar deluxe');

        $media = $this->hotel->media()->first();

        $this->assertNotNull($media);
        $this->assertSame('kamar.jpg', $media->name);
        $this->assertSame($this->hotelAdmin->id, $media->uploaded_by);
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertTrue(str_starts_with($media->file_path, 'storage/media/'.$this->hotel->id.'/'));

        // File fisik tersimpan pada disk public milik tenant
        Storage::disk('public')->assertExists(substr($media->file_path, strlen('storage/')));

        // Jika thumbnail digenerate, file thumbnail harus benar-benar ada
        $media->refresh();
        if ($media->thumb_path) {
            Storage::disk('public')->assertExists(substr($media->thumb_path, strlen('storage/')));
        }
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');

        $this->upload(UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'))
            ->assertSessionHasErrors('file');

        $this->assertSame(0, $this->hotel->media()->count());
    }

    public function test_media_list_returns_json_for_picker(): void
    {
        $media = TenantMedia::factory()->create([
            'tenant_id' => $this->hotel->id,
            'name' => 'kamar.jpg',
            'alt_text' => 'Kamar deluxe',
        ]);

        $this->actingAs($this->hotelAdmin)
            ->get('http://hotel.profil.cloud/admin/media/list')
            ->assertOk()
            ->assertJsonPath('data.0.id', $media->id)
            ->assertJsonPath('data.0.name', 'kamar.jpg')
            ->assertJsonPath('data.0.alt', 'Kamar deluxe');
    }

    public function test_admin_can_update_alt_text(): void
    {
        $media = TenantMedia::factory()->create(['tenant_id' => $this->hotel->id]);

        $this->actingAs($this->hotelAdmin)
            ->patch("http://hotel.profil.cloud/admin/media/{$media->id}", ['alt_text' => 'Fasilitas kolam renang'])
            ->assertRedirect();

        $this->assertDatabaseHas('tenant_media', ['id' => $media->id, 'alt_text' => 'Fasilitas kolam renang']);
    }

    public function test_unused_media_can_be_deleted_with_files(): void
    {
        Storage::fake('public');

        $this->upload(UploadedFile::fake()->image('kolam.jpg', 320, 240))->assertOk();

        $media = $this->hotel->media()->first();
        $relative = substr($media->file_path, strlen('storage/'));

        Storage::disk('public')->assertExists($relative);

        $this->actingAs($this->hotelAdmin)
            ->delete("http://hotel.profil.cloud/admin/media/{$media->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tenant_media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($relative);
    }

    public function test_media_used_as_cover_cannot_be_deleted(): void
    {
        $media = TenantMedia::factory()->create(['tenant_id' => $this->hotel->id]);

        TenantPost::factory()->create([
            'tenant_id' => $this->hotel->id,
            'cover_media_id' => $media->id,
        ]);

        $this->actingAs($this->hotelAdmin)
            ->delete("http://hotel.profil.cloud/admin/media/{$media->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tenant_media', ['id' => $media->id]);
    }

    public function test_media_is_isolated_between_tenants(): void
    {
        TenantMedia::factory()->create([
            'tenant_id' => $this->hotel->id,
            'name' => 'media-hotel.jpg',
        ]);

        TenantMedia::factory()->create([
            'tenant_id' => $this->umkm->id,
            'name' => 'media-umkm.jpg',
        ]);

        // Admin hotel tidak bisa membuka panel media tenant lain
        $this->actingAs($this->hotelAdmin)
            ->get('http://umkm.profil.cloud/admin/media')
            ->assertForbidden();

        // Daftar media hotel hanya memuat media miliknya
        $this->actingAs($this->hotelAdmin)
            ->get('http://hotel.profil.cloud/admin/media/list')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'media-hotel.jpg');
    }

    public function test_admin_cannot_upload_to_other_tenants_media(): void
    {
        Storage::fake('public');

        // Admin hotel mencoba upload ke subdomain UMKM
        $this->actingAs($this->hotelAdmin)
            ->post('http://umkm.profil.cloud/admin/media', [
                'file' => UploadedFile::fake()->image('jahilan.jpg', 320, 240),
            ])
            ->assertForbidden();

        $this->assertSame(0, $this->umkm->media()->count());
        $this->assertSame(0, $this->hotel->media()->count());
    }
}
