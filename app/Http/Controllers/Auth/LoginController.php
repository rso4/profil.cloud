<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Arahkan sesuai role
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }

            // Tenant admin: pastikan akses hanya pada subdomain tenant-nya
            $tenant = current_tenant();
            if ($tenant && $user->tenant_id === $tenant->id) {
                return redirect()->route('tenant.dashboard');
            }

            // Jika tenant admin login dari domain utama, arahkan ke subdomain-nya
            if ($user->tenant) {
                return redirect()->to('https://'.$user->tenant->slug.'.'.config('app.base_domain').'/admin');
            }

            Auth::logout();
            return back()->withErrors(['email' => 'Akun tidak memiliki akses.']);
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
