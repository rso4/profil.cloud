@extends('admin.layout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Galeri</h1>

<div class="bg-white rounded-xl p-6 shadow-sm mb-6 max-w-2xl">
    <h2 class="text-lg font-semibold mb-4">Tambah Gambar</h2>
    <form method="POST" action="{{ route('tenant.galleries.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Judul</label>
            <input type="text" name="title" class="w-full border border-gray-300 rounded-lg px-4 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Upload File Gambar</label>
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            <p class="text-xs text-gray-500 mt-1">Maks 5MB. Format: JPG, PNG, WEBP, GIF.</p>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Atau URL Gambar</label>
            <input type="url" name="image" placeholder="https://..." class="w-full border border-gray-300 rounded-lg px-4 py-2">
            <p class="text-xs text-gray-500 mt-1">Isi salah satu: upload file <strong>atau</strong> URL.</p>
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium">Tambah</button>
    </form>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Daftar Galeri</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="gallery-grid">
        @forelse($galleries as $gallery)
            <div class="relative group">
                <button type="button" class="gallery-item block w-full text-left" data-fullscreen data-src="{{ $gallery->image_url }}" data-title="{{ $gallery->title }}">
                    <img src="{{ $gallery->thumb_url }}" alt="{{ $gallery->title }}" loading="lazy"
                         class="w-full h-40 object-cover rounded-lg cursor-zoom-in"
                         onerror="this.onerror=null;this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23e5e7eb%22/><text x=%2250%25%22 y=%2250%25%22 fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2216%22 text-anchor=%22middle%22>Gambar tidak tersedia</text></svg>';">
                </button>
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition rounded-lg flex items-center justify-center gap-2">
                    <button type="button" class="gallery-fullscreen bg-white text-gray-800 px-3 py-2 rounded-lg text-sm font-medium" data-fullscreen data-src="{{ $gallery->image_url }}" data-title="{{ $gallery->title }}">⛶ Fullscreen</button>
                    <button type="button" onclick="openEditModal(this)" data-gallery="{{ json_encode($gallery->only(['id', 'title', 'image'])) }}" class="bg-white text-gray-800 px-4 py-2 rounded-lg text-sm">Edit</button>
                    <form method="POST" action="{{ route('tenant.galleries.destroy', $gallery->id) }}" onsubmit="return confirm('Hapus gambar ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm">Hapus</button>
                    </form>
                </div>
                <p class="text-sm text-gray-500 mt-1 truncate">{{ $gallery->title }}</p>
            </div>
        @empty
            <p class="col-span-4 text-center text-gray-500 py-8">Belum ada gambar.</p>
        @endforelse
    </div>
</div>

{{-- Modal Fullscreen --}}
<div id="fullscreen-modal" class="fixed inset-0 z-50 hidden bg-black/90 items-center justify-center">
    <button type="button" id="fullscreen-close" class="absolute top-4 right-4 text-white text-3xl leading-none w-10 h-10 hover:bg-white/10 rounded-full">&times;</button>
    <button type="button" id="fullscreen-toggle" class="absolute bottom-4 right-4 bg-white/10 text-white px-4 py-2 rounded-lg text-sm hover:bg-white/20">⛶ Layar Penuh</button>
    <img id="fullscreen-image" src="" alt="" class="max-w-full max-h-full object-contain">
    <p id="fullscreen-title" class="absolute bottom-4 left-4 text-white text-sm"></p>
</div>

{{-- Modal Edit --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden bg-black/50 items-center justify-center p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Edit Gambar</h2>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" id="edit-form" data-update-url="{{ route('tenant.galleries.update', ['id' => '__ID__']) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Judul</label>
                <input type="text" name="title" id="edit-title" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Ganti Gambar (opsional)</label>
                <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengganti. Maks 5MB: JPG, PNG, WEBP, GIF.</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Atau URL Gambar Baru</label>
                <input type="url" name="image" id="edit-image" placeholder="https://..." class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk mempertahankan gambar saat ini.</p>
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
    var gallery = JSON.parse(btn.getAttribute('data-gallery'));
    var form = document.getElementById('edit-form');
    form.action = form.getAttribute('data-update-url').replace('__ID__', gallery.id);

    document.getElementById('edit-title').value = gallery.title || '';
    document.getElementById('edit-image').value = '';

    var modal = document.getElementById('edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    var modal = document.getElementById('edit-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Tutup modal edit dengan ESC (modal fullscreen punya handler terpisah)
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !document.getElementById('edit-modal').classList.contains('hidden')) {
        closeEditModal();
    }
});
</script>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    var modal = document.getElementById('fullscreen-modal');
    var modalImg = document.getElementById('fullscreen-image');
    var modalTitle = document.getElementById('fullscreen-title');
    var closeBtn = document.getElementById('fullscreen-close');
    var toggleBtn = document.getElementById('fullscreen-toggle');

    function openFullscreen(src, title) {
        modalImg.src = src;
        modalImg.alt = title || '';
        modalTitle.textContent = title || '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeFullscreen() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        if (document.fullscreenElement) {
            document.exitFullscreen().catch(function () {});
        }
    }

    // Delegasi klik untuk semua elemen dengan data-fullscreen
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-fullscreen]');
        if (trigger) {
            e.preventDefault();
            openFullscreen(trigger.getAttribute('data-src'), trigger.getAttribute('data-title'));
        }
    });

    closeBtn.addEventListener('click', closeFullscreen);

    // Toggle Fullscreen API (cross-browser)
    toggleBtn.addEventListener('click', function () {
        if (document.fullscreenElement) {
            document.exitFullscreen().catch(function () {});
        } else {
            var el = modal;
            var fn = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
            if (fn) {
                fn.call(el);
            }
        }
    });

    // Tutup dengan tombol ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeFullscreen();
        }
    });

    // Tutup saat klik di luar gambar
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeFullscreen();
        }
    });
})();
</script>
@endpush
