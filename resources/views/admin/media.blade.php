@extends('admin.layout')

@section('title', 'Media Library')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold">Media Library</h1>
        <p class="text-sm text-gray-500 mt-1">Gambar yang diunggah dapat dipakai sebagai cover artikel, gambar di konten, atau galeri.</p>
    </div>
    <p class="text-sm text-gray-400">Total {{ $media->total() }} file</p>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm mb-6">
    <h2 class="text-lg font-semibold mb-2">Upload Media Baru</h2>
    <p class="text-xs text-gray-500 mb-4">Format JPG, PNG, WebP, atau GIF. Maksimal 5 MB per file. Media yang masih dipakai artikel/halaman tidak dapat dihapus.</p>
    <form id="media-upload-form" class="flex flex-wrap items-center gap-3">
        <input type="file" id="media-upload-file" accept="image/jpeg,image/png,image/webp,image/gif" required class="text-sm flex-1 min-w-[200px]">
        <input type="text" id="media-upload-alt" placeholder="Alt text (opsional)" maxlength="255" class="border border-gray-300 rounded-lg px-4 py-2 text-sm flex-1 min-w-[160px]">
        <button type="submit" id="media-upload-btn" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-medium whitespace-nowrap">Upload</button>
    </form>
</div>

@if($media->count())
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($media as $item)
            <div class="bg-white rounded-xl p-4 shadow-sm flex flex-col">
                <a href="{{ $item->url }}" target="_blank" title="Lihat ukuran penuh">
                    <img src="{{ $item->thumb_url }}" alt="{{ $item->alt_text ?: $item->name }}" class="w-full h-36 object-cover rounded-lg mb-3" loading="lazy">
                </a>
                <p class="text-sm font-medium truncate" title="{{ $item->name }}">{{ $item->name }}</p>
                <p class="text-xs text-gray-400 mb-3">{{ number_format($item->size / 1024) }} KB &middot; {{ $item->created_at->format('d/m/Y') }}</p>

                <form method="POST" action="{{ route('tenant.media.update', $item->id) }}" class="mb-2">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="alt_text" value="{{ $item->alt_text }}" maxlength="255" placeholder="Alt text..."
                        class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs mb-1.5">
                    <button type="submit" class="w-full border border-gray-300 rounded-lg py-1.5 text-xs hover:bg-gray-50">Simpan Alt Text</button>
                </form>

                <div class="flex gap-2">
                    <button type="button" class="flex-1 border border-gray-300 rounded-lg py-1.5 text-xs hover:bg-gray-50" data-copy="{{ $item->url }}">Copy URL</button>
                    <form method="POST" action="{{ route('tenant.media.destroy', $item->id) }}" onsubmit="return confirm('Hapus media \'{{ $item->name }}\'?')" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-red-600 border border-red-200 rounded-lg py-1.5 text-xs hover:bg-red-50">Hapus</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $media->links() }}</div>
@else
    <div class="bg-white rounded-xl p-10 text-center text-gray-500 shadow-sm">Belum ada media. Unggah gambar pertama Anda di atas.</div>
@endif
@endsection

@push('scripts')
<script>
// Upload via AJAX, lalu muat ulang halaman
document.getElementById('media-upload-form').addEventListener('submit', function (e) {
    e.preventDefault();

    var fileInput = document.getElementById('media-upload-file');
    var altInput = document.getElementById('media-upload-alt');
    var btn = document.getElementById('media-upload-btn');

    if (!fileInput.files.length) {
        return;
    }

    var data = new FormData();
    data.append('file', fileInput.files[0]);
    data.append('alt_text', altInput.value);

    btn.disabled = true;
    btn.textContent = 'Mengunggah...';

    fetch("{{ route('tenant.media.store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: data
    }).then(function (res) {
        if (res.ok) {
            window.location.reload();
            return null;
        }
        return res.json().then(function (json) {
            var errors = json.errors || {};
            alert(Object.values(errors)[0] || 'Upload gagal. Periksa format dan ukuran file.');
        });
    }).catch(function () {
        alert('Upload gagal. Silakan coba lagi.');
    }).finally(function () {
        btn.disabled = false;
        btn.textContent = 'Upload';
    });
});

// Salin URL media ke clipboard
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-copy]');
    if (!btn) {
        return;
    }

    navigator.clipboard.writeText(btn.getAttribute('data-copy')).then(function () {
        var original = btn.textContent;
        btn.textContent = 'Tersalin!';
        setTimeout(function () { btn.textContent = original; }, 1500);
    });
});
</script>
@endpush
