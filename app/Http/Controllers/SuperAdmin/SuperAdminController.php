<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'templates' => Template::count(),
            'users' => User::count(),
        ];

        $recentTenants = Tenant::latest()->take(5)->get();

        return view('superadmin.dashboard', compact('stats', 'recentTenants'));
    }

    // ===== Manajemen Tenant =====
    public function tenants()
    {
        $tenants = Tenant::with('template')->latest()->get();
        $templates = Template::where('is_active', true)->get();
        return view('superadmin.tenants', compact('tenants', 'templates'));
    }

    /**
     * Cek ketersediaan subdomain (slug) untuk validasi keunikan.
     */
    public function checkSlug(Request $request)
    {
        $slug = $request->query('slug', '');
        $excludeId = $request->query('exclude', null);

        $exists = Tenant::where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        return response()->json(['available' => !$exists]);
    }

    public function storeTenant(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug|regex:/^[a-z0-9-]+$/',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'template_slug' => 'required|exists:templates,slug',
            'primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'admin_phone' => 'nullable|string|max:50',
        ]);

        $tenant = Tenant::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'template_slug' => $data['template_slug'],
            'primary_color' => $data['primary_color'],
            'secondary_color' => $data['secondary_color'],
            'is_active' => true,
        ]);

        // Buat profil kosong
        $tenant->profile()->create([]);

        // Buat admin tenant
        User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'phone' => $data['admin_phone'] ?? null,
            'password' => Hash::make($data['admin_password']),
            'tenant_id' => $tenant->id,
            'role' => 'tenant_admin',
        ]);

        return back()->with('success', "Tenant '{$tenant->name}' berhasil dibuat. Subdomain: {$tenant->slug}.profil.cloud");
    }

    // ===== Manajemen User =====
    public function users()
    {
        $users = User::with('tenant')->latest()->get();
        $tenants = Tenant::orderBy('name')->get();

        return view('superadmin.users', compact('users', 'tenants'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,tenant_admin',
            'tenant_id' => 'nullable|exists:tenants,id',
        ]);

        $data['password'] = Hash::make($data['password']);
        if ($data['role'] === 'super_admin') {
            $data['tenant_id'] = null;
        }

        User::create($data);

        return back()->with('success', "User '{$data['name']}' berhasil ditambahkan.");
    }

    public function editUser(User $user)
    {
        return view('superadmin.user-edit', [
            'user' => $user,
            'tenants' => Tenant::orderBy('name')->get(),
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:super_admin,tenant_admin',
            'tenant_id' => 'nullable|exists:tenants,id',
        ]);

        if ($data['role'] === 'super_admin') {
            $data['tenant_id'] = null;
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return back()->with('success', "User '{$user->name}' berhasil diperbarui.");
    }

    public function destroyUser(User $user)
    {
        if ($user->is(Auth::user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return back()->with('success', "User '{$user->name}' berhasil dihapus.");
    }

    public function toggleTenant(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);
        return back()->with('success', "Status tenant '{$tenant->name}' diperbarui.");
    }

    /**
     * Tampilkan form edit tenant lengkap (data utama, subdomain, template,
     * dan admin tenant).
     */
    public function editTenant(Tenant $tenant)
    {
        $templates = Template::where('is_active', true)->get();
        $admin = $tenant->users()->where('role', 'tenant_admin')->first();

        return view('superadmin.tenant-edit', compact('tenant', 'templates', 'admin'));
    }

    /**
     * Simpan perubahan data tenant, subdomain, template, dan admin.
     */
    public function updateTenant(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            // Data utama tenant
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/|unique:tenants,slug,' . $tenant->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'template_slug' => 'required|exists:templates,slug',
            'primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'nullable|boolean',
            // Profil tenant
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            // Admin tenant
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email,' . ($tenant->users()->where('role', 'tenant_admin')->value('id') ?? 'NULL'),
            'admin_phone' => 'nullable|string|max:50',
            'admin_password' => 'nullable|string|min:8',
        ]);

        // Update data utama tenant
        $tenant->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'template_slug' => $data['template_slug'],
            'primary_color' => $data['primary_color'],
            'secondary_color' => $data['secondary_color'],
            'is_active' => $request->boolean('is_active'),
        ]);

        // Update profil tenant
        if ($tenant->profile) {
            $tenant->profile->update([
                'tagline' => $data['tagline'] ?? null,
                'description' => $data['description'] ?? null,
                'website' => $data['website'] ?? null,
                'instagram' => $data['instagram'] ?? null,
                'facebook' => $data['facebook'] ?? null,
            ]);
        }

        // Update admin tenant
        $admin = $tenant->users()->where('role', 'tenant_admin')->first();
        if ($admin) {
            $admin->update([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'phone' => $data['admin_phone'] ?? null,
            ]);
            if (!empty($data['admin_password'])) {
                $admin->update(['password' => Hash::make($data['admin_password'])]);
            }
        }

        return redirect()->route('superadmin.tenants')
            ->with('success', "Tenant '{$tenant->name}' berhasil diperbarui.");
    }

    public function destroyTenant(Tenant $tenant)
    {
        $tenant->delete();
        return back()->with('success', "Tenant '{$tenant->name}' berhasil dihapus.");
    }

    // ===== Manajemen Template =====
    public function templates()
    {
        $templates = Template::latest()->get();
        return view('superadmin.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|string|max:255|unique:templates,slug|regex:/^[a-z0-9-]+$/',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:hotel,school,sme,general',
            'thumbnail' => 'nullable|url|max:2048',
        ]);

        Template::create($data);

        return back()->with('success', "Template '{$data['name']}' berhasil ditambahkan.");
    }

    public function toggleTemplate(Template $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return back()->with('success', "Status template '{$template->name}' diperbarui.");
    }
}
