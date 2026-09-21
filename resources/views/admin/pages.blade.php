@extends('admin.layout')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Halaman Custom</h1>
    <a href="/" target="_blank" class="text-sm text-primary hover:underline">Lihat Website &rarr;</a>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm mb-6 max-w-2xl">
    <h2 class="text-lg font-semibold mb-4">Tambah Halaman Baru</h2>
    <form method="POST" action="{{ route('tenant.pages.store') }}" enctype="multipart/form-data" id="add-form">
        @csrf
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Judul Halaman <span class="text-red-500">*</span></label>
                <input type="text" name="title" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="Tentang Kami, Karier, dll.">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Slug URL (opsional)</label>
                <input type="text" name="slug" pattern="[a-z0-9\-]+" maxlength="255" class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="otomatis dari judul">
                <p class="text-xs text-gray-500 mt-1">URL: /p/<span id="slug-preview">slug</span></p>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Konten</label>
            <textarea name="content" rows="8" class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="Tulis konten halaman di sini..."></textarea>
        </div>
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Gambar Cover (opsional)</label>
                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Urutan Tampil</label>
                <input type="number" name="sort_order" value="0" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="flex items-center gap-6 mb-4">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="show_in_nav" value="1" checked class="rounded"> Tampilkan di menu navigasi website
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_published" value="1" checked class="rounded"> Publikasikan
            </label>
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium">Tambah Halaman</button>
    </form>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Daftar Halaman ({{ $pages->count() }})</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">Judul</th>
                    <th class="py-2">URL</th>
                    <th class="py-2">Navigasi</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Urutan</th>
                    <th class="py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                    <tr class="border-b">
                        <td class="py-3 font-medium">{{ $page->title }}</td>
                        <td class="py-3 text-gray-500">/p/{{ $page->slug }}</td>
                        <td class="py-3">{{ $page->show_in_nav ? 'Ya' : 'Tidak' }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $page->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $page->is_published ? 'Publik' : 'Draf' }}
                            </span>
                        </td>
                        <td class="py-3">{{ $page->sort_order }}</td>
                        <td class="py-3">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="openEditModal(this)" data-page="{{ json_encode($page->toArray()) }}" class="text-primary hover:underline">Edit</button>
                                <form method="POST" action="{{ route('tenant.pages.destroy', $page->id) }}" onsubmit="return confirm('Hapus halaman \'{{ $page->title }}\'?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-gray-500">Belum ada halaman custom.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Edit --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden bg-black/50 items-center justify-center p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Edit Halaman</h2>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" id="edit-form" data-update-url="{{ route('tenant.pages.update', ['id' => '__ID__']) }}">
            @csrf
            @method('POST')
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required id="edit-title" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Slug URL</label>
                    <input type="text" name="slug" pattern="[a-z0-9\-]+" id="edit-slug" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Konten</label>
                <x-rich-editor name="content" target="edit-content" placeholder="Tulis konten halaman di sini..." />
            </div>
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Ganti Cover (opsional)</label>
                    @if($pages->first()?->cover_image)
                        <p class="text-xs text-gray-400 mb-1">Kosongkan jika tidak ingin mengganti</p>
                    @endif
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Urutan Tampil</label>
                    <input type="number" name="sort_order" id="edit-sort" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>
            <div class="flex items-center gap-6 mb-4">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="show_in_nav" value="1" id="edit-nav" class="rounded"> Tampilkan di navigasi
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_published" value="1" id="edit-published" class="rounded"> Publikasikan
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium">Simpan Perubahan</button>
                <button type="button" onclick="closeEditModal()" class="border border-gray-300 px-6 py-2 rounded-lg">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditModal(btn) {
    var page = JSON.parse(btn.getAttribute('data-page'));
    var form = document.getElementById('edit-form');
    form.action = form.getAttribute('data-update-url').replace('__ID__', page.id);

    document.getElementById('edit-title').value = page.title || '';
    document.getElementById('edit-slug').value = page.slug || '';
    // Isi rich editor modal (target 'edit-content' agar tidak bentrok dgn form tambah)
    window.RichEditor.set('edit-content', page.content || '');
    document.getElementById('edit-sort').value = page.sort_order || 0;
    document.getElementById('edit-nav').checked = page.show_in_nav == 1;
    document.getElementById('edit-published').checked = page.is_published == 1;

    var modal = document.getElementById('edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    var modal = document.getElementById('edit-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Preview slug otomatis dari judul (form tambah)
document.getElementById('add-form').querySelector('input[name="title"]').addEventListener('input', function () {
    var slug = this.value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    document.getElementById('slug-preview').textContent = slug || 'slug';
});

// Tutup modal dengan ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>
@endpush