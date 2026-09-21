<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Mencocokkan subdomain dengan tenant di database.
     * Menyimpan tenant aktif ke dalam container aplikasi agar
     * seluruh query dapat di-scope dengan tenant_id.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Baca host dari server variable (HTTP_HOST) agar konsisten
        // antara lingkungan produksi dan pengujian.
        $host = $request->server('HTTP_HOST', $request->getHost());
        $baseDomain = config('app.base_domain', 'profil.cloud');

        // Ekstrak subdomain (bagian sebelum base domain)
        $subdomain = null;
        if (str_ends_with($host, '.'.$baseDomain)) {
            $subdomain = str_replace('.'.$baseDomain, '', $host);
        }

        // Jika tidak ada subdomain (akses ke domain utama), lanjutkan
        if (!$subdomain || $subdomain === 'www') {
            return $next($request);
        }

        // Cari tenant berdasarkan slug subdomain
        $tenant = Tenant::where('slug', $subdomain)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            abort(404, 'Tenant tidak ditemukan atau tidak aktif.');
        }

        // Simpan tenant aktif ke container
        app()->instance('current_tenant', $tenant);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
