@extends('admin.layout')

@section('title', 'Kategori Artikel')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">Kategori Artikel</h1>
    <p class="text-sm text-gray-500 mt-1">Kategori membantu pengunjung menemukan artikel berdasarkan topik. Dihapusnya kategori tidak menghapus artikelnya.</p>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm mb-6 max-w-xl">
    <h2 class="text-lg font-semibold mb-4">Tambah Kategori Baru</h2>
    <form method="POST" action="{{ route('tenant.categories.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Nama Kategori <span class="text-red-500">*</span></label>
            <input type="text" name="name" required maxlength="255" value="{{ old('name') }}"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 {{ $errors->has('name') ? 'border-red-500' : '' }}"
                placeholder="Berita, Promo, Tips, Pengumuman...">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Deskripsi (opsional)</label>
            <textarea name="description" rows="2" maxlength="1000" class="w-full border border-gray-300 rounded-lg px-4 py-2"
                placeholder="Penjelasan singkat kategori ini...">{{ old('description') }}</textarea>
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium">Tambah Kategori</button>
    </form>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Daftar Kategori ({{ $categories->count() }})</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">Nama</th>
                    <th class="py-2">Slug</th>
                    <th class="py-2">Deskripsi</th>
                    <th class="py-2">Jumlah Artikel</th>
                    <th class="py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="border-b">
                        <td class="py-3 font-medium">{{ $category->name }}</td>
                        <td class="py-3 text-gray-500">/blog/kategori/{{ $category->slug }}</td>
                        <td class="py-3 text-gray-500 max-w-xs truncate" title="{{ $category->description }}">{{ $category->description ?? '—' }}</td>
                        <td class="py-3">{{ $category->posts_count }}</td>
                        <td class="py-3">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="openEditModal(this)"
                                    data-category="{{ json_encode(['id' => $category->id, 'name' => $category->name, 'description' => $category->description]) }}"
                                    class="text-primary hover:underline">Edit</button>
                                <form method="POST" action="{{ route('tenant.categories.destroy', $category->id) }}"
                                    onsubmit="return confirm('Hapus kategori \'{{ $category->name }}\'? {{ $category->posts_count }} artikel akan menjadi tanpa kategori.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-gray-500">Belum ada kategori. Tambahkan kategori pertama Anda di atas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Edit Kategori --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden bg-black/50 items-center justify-center p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Edit Kategori</h2>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <form method="POST" action="" id="edit-form" data-update-url="{{ route('tenant.categories.update', ['id' => '__ID__']) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="name" required maxlength="255" id="edit-name" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="description" rows="2" maxlength="1000" id="edit-description" class="w-full border border-gray-300 rounded-lg px-4 py-2"></textarea>
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
    var category = JSON.parse(btn.getAttribute('data-category'));
    var form = document.getElementById('edit-form');
    form.action = form.getAttribute('data-update-url').replace('__ID__', category.id);

    document.getElementById('edit-name').value = category.name || '';
    document.getElementById('edit-description').value = category.description || '';

    var modal = document.getElementById('edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    var modal = document.getElementById('edit-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>
@endpush
