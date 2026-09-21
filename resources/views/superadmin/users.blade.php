@extends('superadmin.layout')

@section('title', 'Manajemen User')

@section('content')
<h1 class="text-2xl font-bold mb-6">Manajemen User</h1>

@if(session('error'))
    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl p-6 shadow-sm mb-6">
    <h2 class="text-lg font-semibold mb-4">Tambah User Baru</h2>
    <form method="POST" action="{{ route('superadmin.users.store') }}">
        @csrf
        <div class="grid md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required minlength="8" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" id="role" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="tenant_admin">Admin Tenant</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div id="tenantField">
                <label class="block text-sm font-medium mb-1">Tenant</label>
                <select name="tenant_id" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="">— Pilih Tenant —</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">Tambah User</button>
    </form>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Daftar User</h2>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2">Nama</th>
                <th class="py-2">Email</th>
                <th class="py-2">Telepon</th>
                <th class="py-2">Role</th>
                <th class="py-2">Tenant</th>
                <th class="py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr class="border-b">
                    <td class="py-3 font-medium">
                        {{ $user->name }}
                        @if($user->is(Auth::user()))
                            <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">Anda</span>
                        @endif
                    </td>
                    <td class="py-3">{{ $user->email }}</td>
                    <td class="py-3">{{ $user->phone ?? '-' }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $user->role === 'super_admin' ? 'Super Admin' : 'Admin Tenant' }}
                        </span>
                    </td>
                    <td class="py-3">{{ $user->tenant->name ?? '-' }}</td>
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            {{-- Edit --}}
                            <a href="{{ route('superadmin.users.edit', $user->id) }}"
                               title="Edit user"
                               class="p-2 rounded-lg text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800 transition-colors duration-200"
                               aria-label="Edit user {{ $user->name }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Hapus (tombol disabled untuk akun sendiri) --}}
                            @if($user->is(Auth::user()))
                                <span title="Tidak dapat menghapus akun sendiri"
                                      class="p-2 rounded-lg text-gray-300 cursor-not-allowed"
                                      aria-disabled="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </span>
                            @else
                                <form method="POST" action="{{ route('superadmin.users.destroy', $user->id) }}" onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Hapus user"
                                            class="p-2 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-800 transition-colors duration-200"
                                            aria-label="Hapus user {{ $user->name }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-4 text-center text-gray-500">Belum ada user.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    // Tampilkan pilihan tenant hanya untuk role tenant_admin
    (function () {
        var roleSelect = document.getElementById('role');
        var tenantField = document.getElementById('tenantField');
        var tenantSelect = tenantField ? tenantField.querySelector('select') : null;

        function updateTenantVisibility() {
            var isTenantAdmin = roleSelect.value === 'tenant_admin';
            tenantField.style.display = isTenantAdmin ? '' : 'none';
            if (tenantSelect) {
                tenantSelect.disabled = !isTenantAdmin;
                if (!isTenantAdmin) tenantSelect.value = '';
            }
        }

        roleSelect.addEventListener('change', updateTenantVisibility);
        updateTenantVisibility();
    })();
</script>
@endsection