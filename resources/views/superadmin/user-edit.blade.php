@extends('superadmin.layout')

@section('title', 'Edit User')

@section('content')
<h1 class="text-2xl font-bold mb-6">Edit User: {{ $user->name }}</h1>

<div class="bg-white rounded-xl p-6 shadow-sm max-w-2xl">
    <form method="POST" action="{{ route('superadmin.users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="grid gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password Baru <span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" minlength="8" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" id="role" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="tenant_admin" {{ old('role', $user->role) === 'tenant_admin' ? 'selected' : '' }}>Admin Tenant</option>
                    <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>
            <div id="tenantField">
                <label class="block text-sm font-medium mb-1">Tenant</label>
                <select name="tenant_id" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="">— Pilih Tenant —</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ old('tenant_id', $user->tenant_id) == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">Simpan Perubahan</button>
            <a href="{{ route('superadmin.users') }}" class="text-gray-500 hover:text-gray-700">Batal</a>
        </div>
    </form>
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