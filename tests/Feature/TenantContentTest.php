<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantContentTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $template = Template::factory()->create(['slug' => 'hotel-01']);
        $this->tenant = Tenant::factory()->create(['slug' => 'hotel', 'template_slug' => 'hotel-01']);
        $this->admin = User::factory()->tenantAdmin($this->tenant->id)->create();
    }

    private function tenantRequest(string $method, string $uri, array $data = [])
    {
        return $this->actingAs($this->admin)
            ->{$method}('http://hotel.profil.cloud'.$uri, $data);
    }

    public function test_tenant_can_update_profile(): void
    {
        $response = $this->tenantRequest('post', '/admin/profile', [
            'name' => 'Hotel Baru',
            'tagline' => 'Tagline baru',
            'description' => 'Deskripsi baru',
            'website' => 'https://example.com',
            'instagram' => '@test',
            'facebook' => 'fb',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'id' => $this->tenant->id,
            'name' => 'Hotel Baru',
        ]);
        $this->assertDatabaseHas('tenant_profiles', [
            'tenant_id' => $this->tenant->id,
            'tagline' => 'Tagline baru',
        ]);
    }

    public function test_tenant_can_update_theme(): void
    {
        $response = $this->tenantRequest('post', '/admin/theme', [
            'primary_color' => '#ff0000',
            'secondary_color' => '#000000',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'id' => $this->tenant->id,
            'primary_color' => '#ff0000',
            'secondary_color' => '#000000',
        ]);
    }

    public function test_theme_validation_rejects_invalid_color(): void
    {
        $response = $this->tenantRequest('post', '/admin/theme', [
            'primary_color' => 'not-a-color',
            'secondary_color' => '#000000',
        ]);

        $response->assertSessionHasErrors('primary_color');
    }

    public function test_tenant_can_create_service(): void
    {
        $response = $this->tenantRequest('post', '/admin/services', [
            'name' => 'Layanan Baru',
            'description' => 'Deskripsi',
            'icon' => '🏨',
            'price' => 100000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenant_services', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Layanan Baru',
        ]);
    }

    public function test_tenant_can_update_own_service(): void
    {
        $service = $this->tenant->services()->create([
            'name' => 'Layanan Awal',
            'description' => 'Deskripsi awal',
            'price' => 50000,
        ]);

        $response = $this->tenantRequest('post', "/admin/services/{$service->id}", [
            'name' => 'Layanan Diperbarui',
            'description' => 'Deskripsi baru',
            'icon' => '⭐',
            'price' => 150000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenant_services', [
            'id' => $service->id,
            'name' => 'Layanan Diperbarui',
            'description' => 'Deskripsi baru',
            'icon' => '⭐',
            'price' => 150000,
        ]);
    }

    public function test_tenant_cannot_update_other_tenant_service(): void
    {
        $template = Template::where('slug', 'hotel-01')->firstOrFail();
        $other = Tenant::factory()->create(['slug' => 'umkm', 'template_slug' => $template->slug]);
        $otherService = $other->services()->create(['name' => 'Layanan Umkm']);

        $this->actingAs($this->admin)
            ->post("http://umkm.profil.cloud/admin/services/{$otherService->id}", [
                'name' => 'Dirubah Jahil',
            ])->assertForbidden();

        $this->assertDatabaseHas('tenant_services', [
            'id' => $otherService->id,
            'name' => 'Layanan Umkm',
        ]);
    }

    public function test_tenant_can_delete_own_service(): void
    {
        $service = $this->tenant->services()->create(['name' => 'Layanan Hapus']);

        $response = $this->tenantRequest('delete', "/admin/services/{$service->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tenant_services', ['id' => $service->id]);
    }

    public function test_tenant_can_create_contact(): void
    {
        $response = $this->tenantRequest('post', '/admin/contacts', [
            'label' => 'Telepon',
            'value' => '+62 812',
            'type' => 'phone',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenant_contacts', [
            'tenant_id' => $this->tenant->id,
            'value' => '+62 812',
        ]);
    }

    public function test_tenant_can_upload_profile_images(): void
    {
        Storage::fake('public');

        $response = $this->tenantRequest('post', '/admin/profile', [
            'name' => 'Hotel Bergambar',
            'tagline' => 'Tagline dengan gambar',
            'cover_image' => UploadedFile::fake()->image('hero.jpg', 1280, 720),
            'about_image' => UploadedFile::fake()->image('tentang.jpg', 800, 800),
        ]);

        $response->assertRedirect();

        $profile = $this->tenant->profile()->first();
        $this->assertNotNull($profile);
        $this->assertStringStartsWith('storage/profiles/'.$this->tenant->id.'/', $profile->cover_image);
        $this->assertStringStartsWith('storage/profiles/'.$this->tenant->id.'/', $profile->about_image);

        // File fisik tersimpan dan URL accessor berfungsi
        Storage::disk('public')->assertExists(substr($profile->cover_image, strlen('storage/')));
        Storage::disk('public')->assertExists(substr($profile->about_image, strlen('storage/')));
        $this->assertSame(asset($profile->cover_image), $profile->cover_image_url);
        $this->assertSame(asset($profile->about_image), $profile->about_image_url);
    }

    public function test_public_homepage_renders_custom_profile_images(): void
    {
        // Template sme-01 mendukung gambar profil kustom (hero + tentang)
        Template::factory()->create(['slug' => 'sme-01']);
        $umkm = Tenant::factory()->create(['slug' => 'umkm', 'template_slug' => 'sme-01']);
        $umkm->profile()->updateOrCreate(['tenant_id' => $umkm->id], [
            'cover_image' => 'storage/profiles/'.$umkm->id.'/hero.jpg',
            'about_image' => 'storage/profiles/'.$umkm->id.'/about.jpg',
        ]);

        $this->get('http://umkm.profil.cloud/')
            ->assertOk()
            ->assertSee(asset('storage/profiles/'.$umkm->id.'/hero.jpg'), false)
            ->assertSee(asset('storage/profiles/'.$umkm->id.'/about.jpg'), false);
    }

    public function test_tenant_can_update_own_contact(): void
    {
        $contact = $this->tenant->contacts()->create([
            'label' => 'Telepon',
            'value' => '+62 812',
            'type' => 'phone',
        ]);

        $response = $this->tenantRequest('post', "/admin/contacts/{$contact->id}", [
            'label' => 'WhatsApp',
            'value' => '+62 813 9999 8888',
            'type' => 'social',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenant_contacts', [
            'id' => $contact->id,
            'label' => 'WhatsApp',
            'value' => '+62 813 9999 8888',
            'type' => 'social',
        ]);
    }

    public function test_tenant_cannot_update_other_tenant_contact(): void
    {
        $other = Tenant::factory()->create(['slug' => 'sekolah']);
        $contact = $other->contacts()->create([
            'label' => 'Telepon Umkm',
            'value' => '+62 811',
            'type' => 'phone',
        ]);

        // ID kontak milik tenant lain tidak ditemukan dalam scope tenant sendiri
        $response = $this->tenantRequest('post', "/admin/contacts/{$contact->id}", [
            'value' => 'Dirubah Jahil',
            'type' => 'phone',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('tenant_contacts', [
            'id' => $contact->id,
            'value' => '+62 811',
        ]);
    }

    public function test_tenant_can_open_contact_edit_page(): void
    {
        $contact = $this->tenant->contacts()->create([
            'label' => 'Telepon',
            'value' => '+62 812',
            'type' => 'phone',
        ]);

        $this->tenantRequest('get', "/admin/contacts/{$contact->id}/edit")
            ->assertOk()
            ->assertSee('Edit Kontak')
            ->assertSee('+62 812');

        // URL format lama tanpa /edit juga menampilkan form edit (alias route)
        $this->tenantRequest('get', "/admin/contacts/{$contact->id}")
            ->assertOk()
            ->assertSee('Edit Kontak');
    }

    public function test_tenant_can_create_gallery(): void
    {
        $response = $this->tenantRequest('post', '/admin/galleries', [
            'title' => 'Foto 1',
            'image' => 'https://example.com/photo.jpg',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenant_galleries', [
            'tenant_id' => $this->tenant->id,
            'title' => 'Foto 1',
        ]);
    }

    public function test_tenant_can_upload_gallery_image_file(): void
    {
        $file = UploadedFile::fake()->image('foto.jpg', 400, 300);

        $response = $this->tenantRequest('post', '/admin/galleries', [
            'title' => 'Foto Upload',
            'image_file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenant_galleries', [
            'tenant_id' => $this->tenant->id,
            'title' => 'Foto Upload',
        ]);

        $gallery = $this->tenant->galleries()->where('title', 'Foto Upload')->first();
        $this->assertNotNull($gallery);
        $this->assertStringStartsWith('storage/galleries/', $gallery->image);
        $this->assertStringStartsWith(asset('storage/galleries/'), $gallery->image_url);
    }

    public function test_gallery_requires_image_or_file(): void
    {
        $response = $this->tenantRequest('post', '/admin/galleries', [
            'title' => 'Tanpa Gambar',
        ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_tenant_can_update_own_gallery_title(): void
    {
        $gallery = $this->tenant->galleries()->create([
            'title' => 'Judul Awal',
            'image' => 'https://example.com/photo.jpg',
        ]);

        $response = $this->tenantRequest('post', "/admin/galleries/{$gallery->id}", [
            'title' => 'Judul Baru',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenant_galleries', [
            'id' => $gallery->id,
            'title' => 'Judul Baru',
            'image' => 'https://example.com/photo.jpg',
        ]);
    }

    public function test_tenant_can_replace_gallery_image_with_upload(): void
    {
        $gallery = $this->tenant->galleries()->create([
            'title' => 'Foto Lama',
            'image' => 'storage/galleries/'.$this->tenant->id.'/lama.jpg',
            'thumb' => 'storage/galleries/'.$this->tenant->id.'/lama-thumb.jpg',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put('galleries/'.$this->tenant->id.'/lama.jpg', 'lama');
        Storage::disk('public')->put('galleries/'.$this->tenant->id.'/lama-thumb.jpg', 'lama');

        $response = $this->tenantRequest('post', "/admin/galleries/{$gallery->id}", [
            'title' => 'Foto Baru',
            'image_file' => UploadedFile::fake()->image('baru.jpg', 640, 480),
        ]);

        $response->assertRedirect();
        $gallery->refresh();

        $this->assertSame('Foto Baru', $gallery->title);
        $this->assertStringStartsWith('storage/galleries/'.$this->tenant->id.'/', $gallery->image);
        $this->assertNotSame('storage/galleries/'.$this->tenant->id.'/lama.jpg', $gallery->image);
        $this->assertStringContainsString('-thumb', $gallery->thumb);

        // File lama terhapus, file baru ada
        Storage::disk('public')->assertMissing('galleries/'.$this->tenant->id.'/lama.jpg');
        Storage::disk('public')->assertExists(substr($gallery->image, strlen('storage/')));
    }

    public function test_tenant_cannot_update_other_tenant_gallery(): void
    {
        $other = Tenant::factory()->create(['slug' => 'sekolah']);
        $gallery = $other->galleries()->create([
            'title' => 'Foto Lain',
            'image' => 'https://example.com/photo.jpg',
        ]);

        // ID galeri milik tenant lain tidak ditemukan dalam scope tenant sendiri
        $response = $this->tenantRequest('post', "/admin/galleries/{$gallery->id}", [
            'title' => 'Dirubah Jahil',
        ]);

        $response->assertNotFound();

        $this->assertDatabaseHas('tenant_galleries', [
            'id' => $gallery->id,
            'title' => 'Foto Lain',
        ]);
    }

    public function test_tenant_can_delete_own_gallery(): void
    {
        $gallery = $this->tenant->galleries()->create([
            'title' => 'Foto Hapus',
            'image' => 'https://example.com/photo.jpg',
        ]);

        $response = $this->tenantRequest('delete', "/admin/galleries/{$gallery->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tenant_galleries', ['id' => $gallery->id]);
    }

    public function test_tenant_cannot_delete_other_tenant_gallery(): void
    {
        $other = Tenant::factory()->create(['slug' => 'sekolah']);
        $gallery = $other->galleries()->create([
            'title' => 'Foto Lain',
            'image' => 'https://example.com/photo.jpg',
        ]);

        $response = $this->tenantRequest('delete', "/admin/galleries/{$gallery->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('tenant_galleries', ['id' => $gallery->id]);
    }

    public function test_public_website_renders_gallery_image_url(): void
    {
        $this->tenant->galleries()->create([
            'title' => 'Foto Publik',
            'image' => 'storage/galleries/1/photo.jpg',
        ]);

        $response = $this->get('http://hotel.profil.cloud/');

        $response->assertOk();
        $response->assertSee(asset('storage/galleries/1/photo.jpg'), false);
        $response->assertSee('data-gallery', false);
    }
}
