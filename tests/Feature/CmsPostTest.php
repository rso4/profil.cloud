<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\TenantPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur CMS: CRUD artikel + kategori oleh admin tenant.
 * Mencakup slug unik, sanitasi XSS, status, soft delete, dan tag.
 */
class CmsPostTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Template::factory()->create(['slug' => 'hotel-01']);
        $this->tenant = Tenant::factory()->create(['slug' => 'hotel', 'template_slug' => 'hotel-01']);
        $this->admin = User::factory()->tenantAdmin($this->tenant->id)->create();
    }

    /**
     * Request ke panel admin CMS pada subdomain tenant sendiri.
     */
    private function adminRequest(string $method, string $uri, array $data = [])
    {
        return $this->actingAs($this->admin)
            ->{$method}('http://hotel.profil.cloud'.$uri, $data);
    }

    public function test_admin_can_view_posts_list(): void
    {
        $post = TenantPost::factory()->create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Promo Kamar Deluxe',
            'status' => TenantPost::STATUS_PUBLISHED,
        ]);

        $this->adminRequest('get', '/admin/posts')
            ->assertOk()
            ->assertSee('Promo Kamar Deluxe');
    }

    public function test_admin_can_create_post_with_auto_slug_tags_and_schedule(): void
    {
        $this->adminRequest('post', '/admin/posts', [
            'title' => 'Paket Staycation Hemat',
            'content' => '<p>Konten artikel pertama.</p>',
            'status' => 'published',
            'tags' => 'promo, staycation',
            'is_featured' => '1',
        ])->assertRedirect();

        $post = $this->tenant->posts()->where('title', 'Paket Staycation Hemat')->first();

        $this->assertNotNull($post);
        $this->assertSame('paket-staycation-hemat', $post->slug);
        $this->assertSame($this->admin->id, $post->author_id);
        $this->assertTrue($post->is_featured);
        $this->assertNotNull($post->published_at);
        $this->assertSame(['promo', 'staycation'], $post->tags()->orderBy('slug')->pluck('name')->all());
    }

    public function test_post_content_is_sanitized_against_xss(): void
    {
        $this->adminRequest('post', '/admin/posts', [
            'title' => 'Artikel Aman',
            'content' => '<p>Konten aman.</p><script>alert(1)</script><a href="javascript:alert(2)">klik</a><p onclick="evil()">Paragraf</p>',
            'status' => 'published',
        ])->assertRedirect();

        $post = $this->tenant->posts()->where('title', 'Artikel Aman')->first();

        $this->assertNotNull($post);
        $this->assertStringNotContainsString('script', $post->content);
        $this->assertStringNotContainsString('alert', $post->content);
        $this->assertStringNotContainsString('javascript:', $post->content);
        $this->assertStringNotContainsString('onclick', $post->content);
        $this->assertStringContainsString('<p>Konten aman.</p>', $post->content);
        $this->assertStringContainsString('Paragraf', $post->content);
    }

    public function test_slug_is_unique_per_tenant(): void
    {
        $this->adminRequest('post', '/admin/posts', ['title' => 'Promo Akhir Tahun', 'status' => 'draft']);
        $this->adminRequest('post', '/admin/posts', ['title' => 'Promo Akhir Tahun', 'status' => 'draft']);

        $slugs = $this->tenant->posts()->orderBy('id')->pluck('slug')->all();

        $this->assertSame(['promo-akhir-tahun', 'promo-akhir-tahun-2'], $slugs);
    }

    public function test_soft_deleted_post_still_occupies_slug(): void
    {
        $post = TenantPost::factory()->create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Artikel Lama',
            'slug' => 'artikel-lama',
        ]);
        $post->delete();

        $this->adminRequest('post', '/admin/posts', ['title' => 'Artikel Lama', 'status' => 'draft']);

        $newPost = $this->tenant->posts()->whereKeyNot($post->id)->first();

        $this->assertNotNull($newPost);
        $this->assertSame('artikel-lama-2', $newPost->slug);
    }

    public function test_admin_can_update_post(): void
    {
        $post = TenantPost::factory()->draft()->create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Judul Awal',
            'slug' => 'judul-awal',
        ]);

        $this->adminRequest('put', "/admin/posts/{$post->id}", [
            'title' => 'Judul Baru',
            'content' => '<p>Isi baru.</p>',
            'status' => 'published',
        ])->assertRedirect();

        $post->refresh();

        $this->assertSame('Judul Baru', $post->title);
        $this->assertSame('judul-baru', $post->slug);
        $this->assertSame(TenantPost::STATUS_PUBLISHED, $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_toggle_publish_switches_status(): void
    {
        $post = TenantPost::factory()->draft()->create(['tenant_id' => $this->tenant->id]);

        $this->adminRequest('post', "/admin/posts/{$post->id}/toggle-publish")->assertRedirect();
        $this->assertSame(TenantPost::STATUS_PUBLISHED, $post->refresh()->status);

        $this->adminRequest('post', "/admin/posts/{$post->id}/toggle-publish")->assertRedirect();
        $this->assertSame(TenantPost::STATUS_DRAFT, $post->refresh()->status);
    }

    public function test_scheduled_status_requires_published_at(): void
    {
        $this->adminRequest('post', '/admin/posts', [
            'title' => 'Artikel Terjadwal',
            'status' => 'scheduled',
        ])->assertSessionHasErrors('published_at');
    }

    public function test_post_goes_to_trash_and_can_be_restored(): void
    {
        $post = TenantPost::factory()->create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Artikel Sampah',
        ]);

        $this->adminRequest('delete', "/admin/posts/{$post->id}")->assertRedirect();
        $this->assertSoftDeleted('tenant_posts', ['id' => $post->id]);

        // Terlihat di sampah, tidak di daftar utama
        $this->adminRequest('get', '/admin/posts/trash')->assertOk()->assertSee('Artikel Sampah');
        $this->adminRequest('get', '/admin/posts')->assertOk()->assertDontSee('Artikel Sampah');

        $this->adminRequest('post', "/admin/posts/{$post->id}/restore")->assertRedirect();
        $this->assertDatabaseHas('tenant_posts', ['id' => $post->id, 'deleted_at' => null]);
    }

    public function test_post_can_be_deleted_permanently(): void
    {
        $post = TenantPost::factory()->create(['tenant_id' => $this->tenant->id]);
        $post->delete();

        $this->adminRequest('delete', "/admin/posts/{$post->id}/force")->assertRedirect();
        $this->assertDatabaseMissing('tenant_posts', ['id' => $post->id]);
    }

    public function test_category_from_other_tenant_is_ignored(): void
    {
        $other = Tenant::factory()->create(['slug' => 'umkm', 'template_slug' => 'hotel-01']);
        $otherCategory = $other->categories()->create([
            'name' => 'Kategori Umkm',
            'slug' => 'kategori-umkm',
        ]);

        $this->adminRequest('post', '/admin/posts', [
            'title' => 'Artikel Hotel',
            'status' => 'draft',
            'category_id' => $otherCategory->id,
        ])->assertRedirect();

        $post = $this->tenant->posts()->where('title', 'Artikel Hotel')->first();

        $this->assertNotNull($post);
        $this->assertNull($post->category_id);
    }

    public function test_tags_are_synced_and_orphans_removed(): void
    {
        $post = TenantPost::factory()->create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Artikel Tags',
            'slug' => 'artikel-tags',
        ]);

        $this->adminRequest('put', "/admin/posts/{$post->id}", [
            'title' => 'Artikel Tags',
            'status' => 'draft',
            'tags' => 'kopi, teh',
        ])->assertRedirect();

        $this->assertSame(['kopi', 'teh'], $post->tags()->orderBy('slug')->pluck('name')->all());
        $this->assertSame(2, $this->tenant->tags()->count());

        // Update: hanya 'kopi' -> tag 'teh' jadi yatim dan dibersihkan
        $this->adminRequest('put', "/admin/posts/{$post->id}", [
            'title' => 'Artikel Tags',
            'status' => 'draft',
            'tags' => 'kopi',
        ])->assertRedirect();

        $this->assertSame(['kopi'], $post->tags()->pluck('name')->all());
        $this->assertSame(1, $this->tenant->tags()->count());
    }

    public function test_admin_can_manage_categories(): void
    {
        $this->adminRequest('post', '/admin/categories', [
            'name' => 'Promosi',
            'description' => 'Artikel promo',
        ])->assertRedirect();

        $category = $this->tenant->categories()->where('slug', 'promosi')->first();
        $this->assertNotNull($category);

        $this->adminRequest('post', "/admin/categories/{$category->id}", [
            'name' => 'Promosi & Diskon',
            'description' => 'Artikel promo dan diskon',
        ])->assertRedirect();

        $this->assertDatabaseHas('tenant_categories', [
            'id' => $category->id,
            'name' => 'Promosi & Diskon',
            'slug' => 'promosi-diskon',
        ]);
    }
}
