@extends('superadmin.layout')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Edit Tenant: {{ $tenant->name }}</h1>
    <a href="{{ route('superadmin.tenants') }}" class="text-blue-600 hover:underline text-sm">← Kembali ke Daftar</a>
</div>

<form method="POST" action="{{ route('superadmin.tenants.update', $tenant->id) }}" id="editForm">
    @csrf
    @method('PUT')

    {{-- ===== 1. Data Utama Tenant ===== --}}
    <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
        <h2 class="text-lg font-semibold mb-4">Data Utama Tenant</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Bisnis</label>
                <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Subdomain (slug)</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $tenant->slug) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <span class="text-gray-500 text-sm whitespace-nowrap">.{{ config('app.base_domain') }}</span>
                </div>
                <p id="slug-status" class="text-xs mt-1"></p>
                @error('slug') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $tenant->email) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Alamat</label>
                <input type="text" name="address" value="{{ old('address', $tenant->address) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('address') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Warna Primer</label>
                    <input type="color" name="primary_color" value="{{ old('primary_color', $tenant->primary_color) }}" class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer">
                    @error('primary_color') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Warna Sekunder</label>
                    <input type="color" name="secondary_color" value="{{ old('secondary_color', $tenant->secondary_color) }}" class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer">
                    @error('secondary_color') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="is_active" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="1" {{ $tenant->is_active ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ !$tenant->is_active ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ===== 2. Profil Tenant ===== --}}
    <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
        <h2 class="text-lg font-semibold mb-4">Profil & Deskripsi</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tagline</label>
                <input type="text" name="tagline" value="{{ old('tagline', $tenant->profile->tagline ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('tagline') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Website</label>
                <input type="url" name="website" value="{{ old('website', $tenant->profile->website ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('website') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2">{{ old('description', $tenant->profile->description ?? '') }}</textarea>
                @error('description') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Instagram</label>
                <input type="text" name="instagram" value="{{ old('instagram', $tenant->profile->instagram ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Facebook</label>
                <input type="text" name="facebook" value="{{ old('facebook', $tenant->profile->facebook ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
        </div>
    </div>

    {{-- ===== 3. Template dengan Pratinjau ===== --}}
    <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
        <h2 class="text-lg font-semibold mb-4">Template Website</h2>
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium mb-1">Pilih Template</label>
                <select name="template_slug" id="template-select" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    @foreach($templates as $template)
                        <option value="{{ $template->slug }}" data-preview="{{ static_image($template->slug, 'preview-thumb', $template->description ?? '') }}" {{ $tenant->template_slug === $template->slug ? 'selected' : '' }}>
                            {{ $template->name }} ({{ $template->slug }})
                        </option>
                    @endforeach
                </select>
                @error('template_slug') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500 mt-2">Pilih template untuk melihat pratinjau sebelum disimpan.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Pratinjau Template</label>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                    @php $previewPath = 'storage/images/templates/' . $tenant->template_slug . '/preview-thumb.jpg'; @endphp
                    <img id="template-preview" src="{{ asset($previewPath) }}" alt="Pratinjau template" class="w-full h-48 object-cover" data-fallback="{{ asset('storage/images/templates/' . $tenant->template_slug . '/thumb.jpg') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ===== 4. Admin Tenant ===== --}}
    <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
        <h2 class="text-lg font-semibold mb-4">Administrator Tenant</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Admin</label>
                <input type="text" name="admin_name" value="{{ old('admin_name', $admin->name ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('admin_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email Admin</label>
                <input type="email" name="admin_email" value="{{ old('admin_email', $admin->email ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('admin_email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Telepon Admin</label>
                <input type="text" name="admin_phone" value="{{ old('admin_phone', $admin->phone ?? '') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('admin_phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password Baru (opsional)</label>
                <input type="password" name="admin_password" placeholder="Kosongkan jika tidak diubah" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                @error('admin_password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Hak Akses</label>
                <input type="text" value="{{ $admin->role ?? 'tenant_admin' }}" disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-gray-500">
                <p class="text-xs text-gray-500 mt-1">Role admin tenant: <code>tenant_admin</code></p>
            </div>
        </div>
    </div>

    {{-- ===== Tombol Aksi ===== --}}
    <div class="flex items-center gap-4">
        <button type="button" onclick="openConfirm()" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700">Simpan Perubahan</button>
        <a href="{{ route('superadmin.tenants') }}" class="text-gray-600 hover:text-gray-800 text-sm">Batal</a>
    </div>
</form>

{{-- Modal Konfirmasi --}}
<div id="confirmModal" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 bg-black/60">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Perubahan</h3>
        <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menyimpan perubahan berikut?</p>
        <div id="confirm-summary" class="bg-gray-50 rounded-lg p-4 mb-4 text-sm text-gray-700 max-h-48 overflow-y-auto"></div>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeConfirm()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</button>
            <button type="button" onclick="submitForm()" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Ya, Simpan</button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // ===== Pratinjau Template =====
    const select = document.getElementById('template-select');
    const preview = document.getElementById('template-preview');
    select.addEventListener('change', function() {
        const opt = select.options[select.selectedIndex];
        preview.src = opt.dataset.preview;
    });
    // Fallback jika gambar preview gagal dimuat
    preview.addEventListener('error', function() {
        if (this.dataset.fallback && this.src !== this.dataset.fallback) {
            this.src = this.dataset.fallback;
        }
    });

    // ===== Validasi Slug Unik (client-side) =====
    const slugInput = document.getElementById('slug');
    const slugStatus = document.getElementById('slug-status');
    const currentSlug = '{{ $tenant->slug }}';
    let slugTimer = null;

    slugInput.addEventListener('input', function() {
        clearTimeout(slugTimer);
        const val = this.value.trim();
        if (!val) { slugStatus.textContent = ''; return; }
        if (!/^[a-z0-9-]+$/.test(val)) {
            slugStatus.textContent = '⚠️ Hanya huruf kecil, angka, dan tanda hubung.';
            slugStatus.className = 'text-xs mt-1 text-red-600';
            return;
        }
        if (val === currentSlug) {
            slugStatus.textContent = 'Subdomain saat ini.';
            slugStatus.className = 'text-xs mt-1 text-gray-500';
            return;
        }
        slugStatus.textContent = 'Memeriksa ketersediaan...';
        slugStatus.className = 'text-xs mt-1 text-gray-500';
        slugTimer = setTimeout(function() {
            fetch('{{ route('superadmin.tenants.check-slug') }}?slug=' + encodeURIComponent(val) + '&exclude={{ $tenant->id }}')
                .then(r => r.json())
                .then(data => {
                    if (data.available) {
                        slugStatus.textContent = '✓ Subdomain tersedia.';
                        slugStatus.className = 'text-xs mt-1 text-green-600';
                    } else {
                        slugStatus.textContent = '✗ Subdomain sudah digunakan tenant lain.';
                        slugStatus.className = 'text-xs mt-1 text-red-600';
                    }
                })
                .catch(() => {
                    slugStatus.textContent = '';
                });
        }, 500);
    });

    // ===== Modal Konfirmasi =====
    window.openConfirm = function() {
        const form = document.getElementById('editForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        // Bangun ringkasan perubahan
        const summary = document.getElementById('confirm-summary');
        const fields = [
            ['Nama', 'name'],
            ['Subdomain', 'slug'],
            ['Email', 'email'],
            ['Telepon', 'phone'],
            ['Template', 'template_slug'],
            ['Nama Admin', 'admin_name'],
            ['Email Admin', 'admin_email'],
        ];
        let html = '<ul class="space-y-1">';
        fields.forEach(([label, name]) => {
            const el = form.querySelector('[name="' + name + '"]');
            if (el) {
                const val = el.value || '-';
                html += '<li><span class="font-medium">' + label + ':</span> ' + val + '</li>';
            }
        });
        html += '</ul>';
        summary.innerHTML = html;
        document.getElementById('confirmModal').classList.remove('hidden');
        document.getElementById('confirmModal').classList.add('flex');
    };

    window.closeConfirm = function() {
        document.getElementById('confirmModal').classList.add('hidden');
        document.getElementById('confirmModal').classList.remove('flex');
    };

    window.submitForm = function() {
        document.getElementById('editForm').submit();
    };

    // Tutup modal saat klik di luar
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) closeConfirm();
    });
})();
</script>
@endsection
