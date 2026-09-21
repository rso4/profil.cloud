<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;

/**
 * Trait isolasi tenant untuk controller panel admin.
 *
 * Memastikan admin tenant hanya dapat mengakses data milik
 * tenant yang cocok dengan subdomain yang sedang dikunjungi.
 */
trait AuthorizesTenant
{
    protected function authorizeTenant(Request $request): void
    {
        $tenant = current_tenant();

        if (!$tenant || $request->user()->tenant_id !== $tenant->id) {
            abort(403, 'Akses lintas tenant ditolak.');
        }
    }
}
