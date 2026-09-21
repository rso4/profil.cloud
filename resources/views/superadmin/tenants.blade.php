@extends('superadmin.layout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Manajemen Tenant</h1>

<div class="bg-white rounded-xl p-6 shadow-sm mb-6">
    <h2 class="text-lg font-semibold mb-4">Buat Tenant Baru</h2>
    <form method="POST" action="{{ route('superadmin.tenants.store') }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Bisnis</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Subdomain (slug)</label>
                <input type="text" name="slug" required placeholder="hotel-anda" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Telepon</label>
                <input type="text" name="phone" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Alamat</label>
                <input type="text" name="address" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Template</label>
                <select name="template_slug" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    @foreach($templates as $template)
                        <option value="{{ $template->slug }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Warna Primer</label>
                    <input type="color" name="primary_color" value="#2563eb" class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Warna Sekunder</label>
                    <input type="color" name="secondary_color" value="#0f172a" class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer">
                </div>
            </div>
        </div>

        <h3 class="text-md font-semibold mb-3 mt-6">Akun Admin Tenant</h3>
        <div class="grid md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Admin</label>
                <input type="text" name="admin_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email Admin</label>
                <input type="email" name="admin_email" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password Admin</label>
                <input type="password" name="admin_password" required minlength="8" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">Buat Tenant</button>
    </form>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Daftar Tenant</h2>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2">Nama</th>
                <th class="py-2">Subdomain</th>
                <th class="py-2">Template</th>
                <th class="py-2">Status</th>
                <th class="py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $tenant)
                <tr class="border-b">
                    <td class="py-3 font-medium">{{ $tenant->name }}</td>
                    <td class="py-3">
                        <a href="https://{{ $tenant->slug }}.{{ config('app.base_domain') }}" target="_blank" class="text-blue-600 hover:underline">
                            {{ $tenant->slug }}.{{ config('app.base_domain') }}
                        </a>
                    </td>
                    <td class="py-3">{{ $tenant->template_slug }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            {{-- Edit --}}
                            <a href="{{ route('superadmin.tenants.edit', $tenant->id) }}"
                               title="Edit tenant"
                               class="p-2 rounded-lg text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800 transition-colors duration-200"
                               aria-label="Edit tenant {{ $tenant->name }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Toggle Aktif/Nonaktif --}}
                            <form method="POST" action="{{ route('superadmin.tenants.toggle', $tenant->id) }}">
                                @csrf
                                <button type="submit"
                                        title="{{ $tenant->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        class="p-2 rounded-lg {{ $tenant->is_active ? 'text-blue-600 hover:bg-blue-50 hover:text-blue-800' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600' }} transition-colors duration-200"
                                        aria-label="{{ $tenant->is_active ? 'Nonaktifkan' : 'Aktifkan' }} tenant {{ $tenant->name }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </button>
                            </form>

                            {{-- Hapus --}}
                            <form method="POST" action="{{ route('superadmin.tenants.destroy', $tenant->id) }}" onsubmit="return confirm('Hapus tenant ini? Semua data akan terhapus.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Hapus tenant"
                                        class="p-2 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-800 transition-colors duration-200"
                                        aria-label="Hapus tenant {{ $tenant->name }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-4 text-center text-gray-500">Belum ada tenant.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
