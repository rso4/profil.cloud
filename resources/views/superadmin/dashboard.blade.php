@extends('superadmin.layout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Dashboard Super Admin</h1>

<div class="grid md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl p-6 shadow-sm">
        <div class="text-3xl font-bold text-blue-600">{{ $stats['tenants'] }}</div>
        <div class="text-gray-500">Total Tenant</div>
    </div>
    <div class="bg-white rounded-xl p-6 shadow-sm">
        <div class="text-3xl font-bold text-green-600">{{ $stats['active_tenants'] }}</div>
        <div class="text-gray-500">Tenant Aktif</div>
    </div>
    <div class="bg-white rounded-xl p-6 shadow-sm">
        <div class="text-3xl font-bold text-purple-600">{{ $stats['templates'] }}</div>
        <div class="text-gray-500">Template</div>
    </div>
    <div class="bg-white rounded-xl p-6 shadow-sm">
        <div class="text-3xl font-bold text-orange-600">{{ $stats['users'] }}</div>
        <div class="text-gray-500">Pengguna</div>
    </div>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Tenant Terbaru</h2>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2">Nama</th>
                <th class="py-2">Subdomain</th>
                <th class="py-2">Template</th>
                <th class="py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentTenants as $tenant)
                <tr class="border-b">
                    <td class="py-3 font-medium">{{ $tenant->name }}</td>
                    <td class="py-3">{{ $tenant->slug }}.{{ config('app.base_domain') }}</td>
                    <td class="py-3">{{ $tenant->template_slug }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-4 text-center text-gray-500">Belum ada tenant.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
