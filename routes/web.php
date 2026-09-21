<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\TenantContentController;
use App\Http\Controllers\Admin\TenantPageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\TemplatePreviewController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ===== Domain Utama (profil.cloud) =====
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Halaman landing domain utama / website tenant
Route::middleware('tenant')->get('/', function () {
    // Jika diakses melalui subdomain tenant, tampilkan website tenant
    if (current_tenant()) {
        return app(WebsiteController::class)->show(request());
    }

    // Jika diakses domain utama, tampilkan landing page
    return view('landing');
})->name('home');

// ===== Panel Super Admin (domain utama) =====
Route::prefix('superadmin')->middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
    Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('superadmin.tenants');
    Route::get('/tenants/check-slug', [SuperAdminController::class, 'checkSlug'])->name('superadmin.tenants.check-slug');
    Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('superadmin.tenants.store');
    Route::get('/tenants/{tenant}/edit', [SuperAdminController::class, 'editTenant'])->name('superadmin.tenants.edit');
    Route::put('/tenants/{tenant}', [SuperAdminController::class, 'updateTenant'])->name('superadmin.tenants.update');
    Route::post('/tenants/{tenant}/toggle', [SuperAdminController::class, 'toggleTenant'])->name('superadmin.tenants.toggle');
    Route::delete('/tenants/{tenant}', [SuperAdminController::class, 'destroyTenant'])->name('superadmin.tenants.destroy');
    Route::get('/templates', [SuperAdminController::class, 'templates'])->name('superadmin.templates');
    Route::post('/templates', [SuperAdminController::class, 'storeTemplate'])->name('superadmin.templates.store');
    Route::post('/templates/{template}/toggle', [SuperAdminController::class, 'toggleTemplate'])->name('superadmin.templates.toggle');

    // Manajemen User
    Route::get('/users', [SuperAdminController::class, 'users'])->name('superadmin.users');
    Route::post('/users', [SuperAdminController::class, 'storeUser'])->name('superadmin.users.store');
    Route::get('/users/{user}/edit', [SuperAdminController::class, 'editUser'])->name('superadmin.users.edit');
    Route::put('/users/{user}', [SuperAdminController::class, 'updateUser'])->name('superadmin.users.update');
    Route::delete('/users/{user}', [SuperAdminController::class, 'destroyUser'])->name('superadmin.users.destroy');
});

// ===== Panel Admin Tenant (subdomain) =====
Route::middleware(['tenant', 'auth', 'role:tenant_admin'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('tenant.dashboard');
    Route::get('/profile', [TenantContentController::class, 'editProfile'])->name('tenant.profile.edit');
    Route::post('/profile', [TenantContentController::class, 'updateProfile'])->name('tenant.profile.update');
    Route::get('/theme', [TenantContentController::class, 'editTheme'])->name('tenant.theme.edit');
    Route::post('/theme', [TenantContentController::class, 'updateTheme'])->name('tenant.theme.update');

    // Layanan
    Route::get('/services', [TenantContentController::class, 'index'])->name('tenant.services');
    Route::post('/services', [TenantContentController::class, 'store'])->name('tenant.services.store');
    Route::post('/services/{id}', [TenantContentController::class, 'update'])->name('tenant.services.update');
    Route::delete('/services/{id}', [TenantContentController::class, 'destroy'])->name('tenant.services.destroy');

    // Galeri
    Route::get('/galleries', [TenantContentController::class, 'galleries'])->name('tenant.galleries');
    Route::post('/galleries', [TenantContentController::class, 'storeGallery'])->name('tenant.galleries.store');
    Route::post('/galleries/{id}', [TenantContentController::class, 'updateGallery'])->name('tenant.galleries.update');
    Route::delete('/galleries/{id}', [TenantContentController::class, 'destroyGallery'])->name('tenant.galleries.destroy');

    // Kontak
    Route::get('/contacts', [TenantContentController::class, 'contacts'])->name('tenant.contacts');
    Route::post('/contacts', [TenantContentController::class, 'storeContact'])->name('tenant.contacts.store');
    // Alias: /admin/contacts/{id} dan /admin/contacts/{id}/edit sama-sama menampilkan form edit
    Route::get('/contacts/{id}', [TenantContentController::class, 'editContact'])->name('tenant.contacts.edit');
    Route::get('/contacts/{id}/edit', [TenantContentController::class, 'editContact']);
    Route::post('/contacts/{id}', [TenantContentController::class, 'updateContact'])->name('tenant.contacts.update');
    Route::delete('/contacts/{id}', [TenantContentController::class, 'destroyContact'])->name('tenant.contacts.destroy');

    // Halaman Custom (konten sendiri milik tenant)
    Route::get('/pages', [TenantPageController::class, 'index'])->name('tenant.pages');
    Route::post('/pages', [TenantPageController::class, 'store'])->name('tenant.pages.store');
    Route::post('/pages/{id}', [TenantPageController::class, 'update'])->name('tenant.pages.update');
    Route::delete('/pages/{id}', [TenantPageController::class, 'destroy'])->name('tenant.pages.destroy');

    // CMS: Artikel
    Route::get('/posts', [PostController::class, 'index'])->name('tenant.posts');
    Route::get('/posts/create', [PostController::class, 'create'])->name('tenant.posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('tenant.posts.store');
    Route::get('/posts/trash', [PostController::class, 'trash'])->name('tenant.posts.trash');
    Route::get('/posts/{id}/edit', [PostController::class, 'edit'])->name('tenant.posts.edit');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('tenant.posts.update');
    Route::post('/posts/{id}/toggle-publish', [PostController::class, 'togglePublish'])->name('tenant.posts.toggle');
    Route::post('/posts/{id}/restore', [PostController::class, 'restore'])->name('tenant.posts.restore');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('tenant.posts.destroy');
    Route::delete('/posts/{id}/force', [PostController::class, 'forceDelete'])->name('tenant.posts.force');

    // CMS: Kategori artikel
    Route::get('/categories', [CategoryController::class, 'index'])->name('tenant.categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('tenant.categories.store');
    Route::post('/categories/{id}', [CategoryController::class, 'update'])->name('tenant.categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('tenant.categories.destroy');

    // CMS: Media library
    Route::get('/media', [MediaController::class, 'index'])->name('tenant.media');
    Route::get('/media/list', [MediaController::class, 'list'])->name('tenant.media.list');
    Route::post('/media', [MediaController::class, 'store'])->name('tenant.media.store');
    Route::patch('/media/{id}', [MediaController::class, 'update'])->name('tenant.media.update');
    Route::delete('/media/{id}', [MediaController::class, 'destroy'])->name('tenant.media.destroy');
});

// ===== Preview Template (untuk screenshot thumbnail/fullscreen) =====
// Dilindungi token rahasia agar tidak dapat diakses publik.
// Didefinisikan SEBELUM route catch-all tenant agar tidak tertangkap.
Route::get('/preview-template/{slug}', [TemplatePreviewController::class, 'show'])
    ->middleware('preview.token')
    ->name('template.preview');

// ===== Website Publik Tenant (subdomain) =====
Route::middleware('tenant')->get('/{any?}', [WebsiteController::class, 'show'])
    ->where('any', '.*')
    ->name('tenant.website');
