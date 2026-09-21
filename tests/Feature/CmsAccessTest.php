<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\TenantPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur CMS: hak akses panel admin tenant (RBAC + isolasi),
 * rendering blog publik, dan halaman custom (sanitasi XSS
 * + backward-compatible plain text).
 */
class CmsAccessTest extends TestCase
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
     * Request sebagai admin hotel ke panel admin subdomain hotel.
     */
    private function asHotelAdmin(string $method, string $uri, array $data = [])
    {
        return $this->actingAs($this->hotelAdmin)
            ->{$method}('http://hotel.profil.cloud'.$uri, $data);
    }

    /**
     * Request publik (tanpa login) ke website tenant hotel.
     */
    private function publicGet(string $uri)
    {
        return $this->get('http://hotel.profil.cloud'.$uri);
    }

    public function test_admin_can_access_all_cms_pages(): void
    {
        foreach (['/admin', '/admin/posts', '/admin/posts/create', '/admin/posts/trash', '/admin/categories', '/admin/media', '/admin/pages'] as $uri) {
            $this->asHotelAdmin('get', $uri)->assertOk();
        }
    }

    public function test_admin_cannot_access_cms_of_other_tenant(): void
    {
        $this->actingAs($this->hotelAdmin)->get('http://umkm.profil.cloud/admin/posts')->assertForbidden();
        $this->actingAs($this->hotelAdmin)->get('http://umkm.profil.cloud/admin/media')->assertForbidden();
    }

    public function test_super_admin_cannot_access_tenant_cms(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get('http://hotel.profil.cloud/admin/posts')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->publicGet('/admin/posts')->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_cms_statistics(): void
    {
        TenantPost::factory()->count(2)->create(['tenant_id' => $this->hotel->id, 'title' => 'Artikel Terbit']);
        TenantPost::factory()->draft()->create(['tenant_id' => $this->hotel->id, 'title' => 'Draf Internal']);
        $this->hotel->pages()->create([
            'title' => 'Tentang',
            'slug' => 'tentang',
            'content' => 'Profil kami.',
            'is_published' => true,
            'show_in_nav' => false,
        ]);

        $this->asHotelAdmin('get', '/admin')
            ->assertOk()
            ->assertSee('Artikel Terbit')
            ->assertSee('Halaman Custom')
            ->assertSee('1 draf menunggu')
            ->assertSee('Draf Internal');
    }

    public function test_blog_index_only_lists_visible_posts(): void
    {
        TenantPost::factory()->create(['tenant_id' => $this->hotel->id, 'title' => 'Promo Terbuka', 'slug' => 'promo-terbuka']);
        TenantPost::factory()->draft()->create(['tenant_id' => $this->hotel->id, 'title' => 'Draf Rahasia', 'slug' => 'draf-rahasia']);
        TenantPost::factory()->scheduled()->create(['tenant_id' => $this->hotel->id, 'title' => 'Terjadwal Depan', 'slug' => 'terjadwal-depan']);

        $this->publicGet('/blog')
            ->assertOk()
            ->assertSee('Promo Terbuka')
            ->assertDontSee('Draf Rahasia')
            ->assertDontSee('Terjadwal Depan');
    }

    public function test_blog_detail_increments_views(): void
    {
        $post = TenantPost::factory()->create([
            'tenant_id' => $this->hotel->id,
            'title' => 'Artikel Dilihat',
            'slug' => 'artikel-dilihat',
        ]);

        $this->publicGet('/blog/artikel-dilihat')->assertOk()->assertSee('Artikel Dilihat');

        $this->assertSame(1, $post->refresh()->views);
    }

    public function test_draft_and_future_scheduled_posts_return_404(): void
    {
        TenantPost::factory()->draft()->create(['tenant_id' => $this->hotel->id, 'slug' => 'draf-rahasia']);
        TenantPost::factory()->scheduled()->create(['tenant_id' => $this->hotel->id, 'slug' => 'terjadwal-depan']);

        $this->publicGet('/blog/draf-rahasia')->assertNotFound();
        $this->publicGet('/blog/terjadwal-depan')->assertNotFound();
    }

    public function test_blog_category_and_tag_pages_work(): void
    {
        $category = $this->hotel->categories()->create(['name' => 'Berita', 'slug' => 'berita']);
        $tag = $this->hotel->tags()->create(['name' => 'Promo', 'slug' => 'promo']);

        $post = TenantPost::factory()->create([
            'tenant_id' => $this->hotel->id,
            'category_id' => $category->id,
            'title' => 'Artikel Kategori',
            'slug' => 'artikel-kategori',
        ]);
        $post->tags()->attach($tag->id);

        $this->publicGet('/blog/kategori/berita')->assertOk()->assertSee('Artikel Kategori');
        $this->publicGet('/blog/kategori/tidak-ada')->assertNotFound();
        $this->publicGet('/blog/tag/promo')->assertOk()->assertSee('Artikel Kategori');
    }

    public function test_public_blog_does_not_render_xss(): void
    {
        $this->asHotelAdmin('post', '/admin/posts', [
            'title' => 'Artikel Publik Aman',
            'content' => '<p>Konten publik aman.</p><script>alert("xss")</script><p onclick="evil()">Paragraf</p>',
            'status' => 'published',
        ])->assertRedirect();

        $post = $this->hotel->posts()->where('title', 'Artikel Publik Aman')->first();
        $this->assertNotNull($post);

        $this->publicGet('/blog/'.$post->slug)
            ->assertOk()
            ->assertSee('Konten publik aman.')
            // Layout situs memuat <script> sah milik aplikasi,
            // jadi asersi menyasar payload XSS-nya, bukan tag generik.
            ->assertDontSee('alert(', false)
            ->assertDontSee('onclick', false);
    }

    public function test_tenant_cannot_delete_other_tenants_post(): void
    {
        $umkmPost = TenantPost::factory()->create([
            'tenant_id' => $this->umkm->id,
            'title' => 'Post Milik Umkm',
            'slug' => 'post-milik-umkm',
        ]);

        $this->actingAs($this->hotelAdmin)
            ->delete("http://umkm.profil.cloud/admin/posts/{$umkmPost->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tenant_posts', ['id' => $umkmPost->id, 'deleted_at' => null]);
    }

    public function test_page_content_is_sanitized_on_store(): void
    {
        $this->asHotelAdmin('post', '/admin/pages', [
            'title' => 'Tentang Kami',
            'content' => '<h2>Profil Singkat</h2><script>alert(1)</script><p onclick="x()">Paragraf halaman.</p>',
        ])->assertRedirect();

        $page = $this->hotel->pages()->where('slug', 'tentang-kami')->first();

        $this->assertNotNull($page);
        $this->assertStringNotContainsString('script', $page->content);
        $this->assertStringNotContainsString('alert', $page->content);
        $this->assertStringNotContainsString('onclick', $page->content);
        $this->assertStringContainsString('<h2>Profil Singkat</h2>', $page->content);
    }

    public function test_public_page_renders_rich_and_plain_content(): void
    {
        // Konten rich text dari editor
        $this->hotel->pages()->create([
            'title' => 'Visi Kami',
            'slug' => 'visi',
            'content' => '<h2>Visi Kami</h2><p>Menjadi yang terdepan.</p>',
            'is_published' => true,
            'show_in_nav' => false,
        ]);

        $this->publicGet('/p/visi')
            ->assertOk()
            ->assertSee('<h2>Visi Kami</h2>', false);

        // Konten plain-text lama tetap terender aman (backward-compatible)
        $this->hotel->pages()->create([
            'title' => 'FAQ',
            'slug' => 'faq',
            'content' => "Pertanyaan pertama\nJawaban pertama",
            'is_published' => true,
            'show_in_nav' => false,
        ]);

        $this->publicGet('/p/faq')
            ->assertOk()
            ->assertSee('Pertanyaan pertama<br', false);
    }

    public function test_unpublished_page_is_not_publicly_visible(): void
    {
        $this->hotel->pages()->create([
            'title' => 'Halaman Rahasia',
            'slug' => 'rahasia',
            'content' => 'Belum tayang.',
            'is_published' => false,
            'show_in_nav' => false,
        ]);

        $this->publicGet('/p/rahasia')->assertNotFound();
    }

    public function test_homepage_shows_blog_link_only_when_posts_exist(): void
    {
        $this->publicGet('/')->assertOk()->assertDontSee('href="/blog"', false);

        TenantPost::factory()->create(['tenant_id' => $this->hotel->id, 'title' => 'Promo Pembuka', 'slug' => 'promo-pembuka']);

        $this->publicGet('/')->assertOk()->assertSee('href="/blog"', false);
    }
}
