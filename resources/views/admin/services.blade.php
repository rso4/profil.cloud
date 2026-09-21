@extends('admin.layout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Layanan</h1>

<div class="bg-white rounded-xl p-6 shadow-sm mb-6 max-w-2xl">
    <h2 class="text-lg font-semibold mb-4">Tambah Layanan</h2>
    <form method="POST" action="{{ route('tenant.services.store') }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Layanan</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Ikon (emoji)</label>
                <input type="text" name="icon" placeholder="🏨" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Harga (opsional)</label>
            <input type="number" name="price" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2">
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium">Tambah</button>
    </form>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Daftar Layanan</h2>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2">Ikon</th>
                <th class="py-2">Nama</th>
                <th class="py-2">Deskripsi</th>
                <th class="py-2">Harga</th>
                <th class="py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
                <tr class="border-b">
                    <td class="py-3 text-xl">{{ $service->icon }}</td>
                    <td class="py-3 font-medium">{{ $service->name }}</td>
                    <td class="py-3 text-gray-500">{{ $service->description }}</td>
                    <td class="py-3">{{ $service->price ? 'Rp '.number_format($service->price, 0, ',', '.') : '-' }}</td>
                    <td class="py-3">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="openEditModal(this)" data-service="{{ json_encode($service->toArray()) }}" class="text-primary hover:underline">Edit</button>
                            <form method="POST" action="{{ route('tenant.services.destroy', $service->id) }}" onsubmit="return confirm('Hapus layanan \'{{ $service->name }}\'?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-4 text-center text-gray-500">Belum ada layanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal Edit --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden bg-black/50 items-center justify-center p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Edit Layanan</h2>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <form method="POST" action="" id="edit-form" data-update-url="{{ route('tenant.services.update', ['id' => '__ID__']) }}">
            @csrf
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Layanan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required id="edit-name" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ikon (emoji)</label>
                    <input type="text" name="icon" id="edit-icon" placeholder="🏨" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="description" rows="3" id="edit-description" class="w-full border border-gray-300 rounded-lg px-4 py-2"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Harga (opsional)</label>
                <input type="number" name="price" step="0.01" min="0" id="edit-price" class="w-full border border-gray-300 rounded-lg px-4 py-2">
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
    var service = JSON.parse(btn.getAttribute('data-service'));
    var form = document.getElementById('edit-form');
    form.action = form.getAttribute('data-update-url').replace('__ID__', service.id);

    document.getElementById('edit-name').value = service.name || '';
    document.getElementById('edit-icon').value = service.icon || '';
    document.getElementById('edit-description').value = service.description || '';
    document.getElementById('edit-price').value = service.price || '';

    var modal = document.getElementById('edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    var modal = document.getElementById('edit-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Tutup modal dengan ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>
@endpush
