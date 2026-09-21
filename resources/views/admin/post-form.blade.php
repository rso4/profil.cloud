@extends('admin.layout')

@section('title', $post ? 'Edit Artikel' : 'Tulis Artikel')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold">{{ $post ? 'Edit Artikel' : 'Tulis Artikel' }}</h1>
        @if($post)
            <p class="text-sm text-gray-500">Terakhir diperbarui {{ $post->updated_at->format('d/m/Y H:i') }}</p>
        @endif
    </div>
    <div class="flex items-center gap-4">
        @if($post?->isPubliclyVisible())
            <a href="https://{{ $tenant->slug }}.{{ config('app.base_domain') }}/blog/{{ $post->slug }}" target="_blank" class="text-sm text-primary hover:underline">Lihat Artikel &rarr;</a>
        @endif
        <a href="{{ route('tenant.posts') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
    </div>
</div>

<form method="POST" action="{{ $post ? route('tenant.posts.update', $post->id) : route('tenant.posts.store') }}" id="post-form">
    @csrf
    @if($post)
        @method('PUT')
    @endif

    @php
        $contentValue = old('content') !== null ? sanitize_html((string) old('content')) : ($post->content ?? '');
        $currentStatus = old('status', $post?->status ?? 'draft');
    @endphp

    <div class="grid lg:grid-cols-3 gap-6 items-start">
        {{-- Kolom utama --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <label class="block text-sm font-medium mb-1">Judul Artikel <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="post-title" required maxlength="255" value="{{ old('title', $post?->title) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-4" placeholder="Judul yang menarik perhatian pembaca...">

                <label class="block text-sm font-medium mb-1">Slug URL (opsional)</label>
                <input type="text" name="slug" pattern="[a-z0-9\-]+" maxlength="255" value="{{ old('slug', $post?->slug) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="otomatis dari judul">
                <p class="text-xs text-gray-500 mt-1">URL: /blog/<span id="slug-preview">{{ $post?->slug ?? 'slug' }}</span></p>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm">
                <label class="block text-sm font-medium mb-2">Konten</label>
                <x-rich-editor name="content" :value="$contentValue" />
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm">
                <label class="block text-sm font-medium mb-1">Ringkasan / Excerpt (opsional)</label>
                <textarea name="excerpt" rows="3" maxlength="1000" class="w-full border border-gray-300 rounded-lg px-4 py-2"
                    placeholder="Kosongkan untuk dibuat otomatis dari konten...">{{ old('excerpt', $post?->excerpt) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Ditampilkan di daftar artikel & hasil pencarian (maks. 200 karakter disarankan).</p>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Kategori</label>
                    <select name="category_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                        <option value="">— Tanpa kategori —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $post?->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Kelola daftar kategori di <a href="{{ route('tenant.categories') }}" class="text-primary hover:underline">menu Kategori</a>.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tag</label>
                    <input type="text" name="tags" maxlength="1000" value="{{ old('tags', $post ? $post->tags->pluck('name')->implode(', ') : '') }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="promo, tips, event">
                    <p class="text-xs text-gray-500 mt-1">Pisahkan dengan koma. Maksimal 20 tag per artikel.</p>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <button type="submit" class="w-full bg-primary text-white px-6 py-2.5 rounded-lg font-medium">{{ $post ? 'Simpan Perubahan' : 'Simpan Artikel' }}</button>

                <label class="block text-sm font-medium mb-1 mt-4">Status</label>
                <select name="status" id="post-status" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white">
                    <option value="draft" @selected($currentStatus === 'draft')>Draf</option>
                    <option value="published" @selected($currentStatus === 'published')>Terbit</option>
                    <option value="scheduled" @selected($currentStatus === 'scheduled')>Terjadwal</option>
                </select>

                <div id="published-at-wrap" class="{{ $currentStatus === 'scheduled' ? '' : 'hidden' }} mt-3">
                    <label class="block text-sm font-medium mb-1">Tanggal &amp; Jam Tayang <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500 mt-1">Artikel otomatis tampil di website setelah waktu ini.</p>
                </div>

                <label class="flex items-center gap-2 text-sm mt-4">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post?->is_featured)) class="rounded">
                    Tandai sebagai artikel unggulan
                </label>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm">
                <label class="block text-sm font-medium mb-2">Gambar Cover</label>
                <div id="cover-preview" class="mb-3 {{ $post?->cover_url ? '' : 'hidden' }}">
                    <img src="{{ $post?->cover_url }}" alt="Preview cover" class="w-full h-36 object-cover rounded-lg">
                </div>
                <input type="hidden" name="cover_media_id" id="cover-media-id" value="{{ old('cover_media_id', $post?->cover_media_id) }}">
                <div class="flex gap-2">
                    <button type="button" id="cover-pick" class="flex-1 border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">Pilih dari Media</button>
                    <button type="button" id="cover-remove" class="px-3 border border-gray-300 rounded-lg py-2 text-sm text-red-600 hover:bg-red-50" title="Hapus cover">Hapus</button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Ukuran disarankan 1600x900 piksel. Upload gambar baru lewat <a href="{{ route('tenant.media') }}" class="text-primary hover:underline">Media Library</a>.</p>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm">
                <h2 class="text-sm font-semibold mb-3">SEO (opsional)</h2>
                <label class="block text-sm font-medium mb-1">Meta Title</label>
                <input type="text" name="meta_title" maxlength="255" value="{{ old('meta_title', $post?->meta_title) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-3 text-sm" placeholder="{{ $post?->title ?? 'Otomatis dari judul' }}">
                <label class="block text-sm font-medium mb-1">Meta Description</label>
                <textarea name="meta_description" rows="3" maxlength="500" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm"
                    placeholder="Otomatis dari ringkasan">{{ old('meta_description', $post?->meta_description) }}</textarea>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Preview slug otomatis dari judul
document.getElementById('post-title').addEventListener('input', function () {
    var slug = this.value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    document.getElementById('slug-preview').textContent = slug || 'slug';
});

// Tampilkan input tanggal tayang hanya untuk status "Terjadwal"
var statusSelect = document.getElementById('post-status');
var publishedWrap = document.getElementById('published-at-wrap');

function syncPublishedAt() {
    publishedWrap.classList.toggle('hidden', statusSelect.value !== 'scheduled');
}
statusSelect.addEventListener('change', syncPublishedAt);
syncPublishedAt();

// Pemilih cover dari media library
var coverInput = document.getElementById('cover-media-id');
var coverPreview = document.getElementById('cover-preview');

document.getElementById('cover-pick').addEventListener('click', function () {
    MediaPicker.open(function (media) {
        coverInput.value = media.id;
        coverPreview.querySelector('img').src = media.url;
        coverPreview.classList.remove('hidden');
    });
});

document.getElementById('cover-remove').addEventListener('click', function () {
    coverInput.value = '';
    coverPreview.querySelector('img').removeAttribute('src');
    coverPreview.classList.add('hidden');
});
</script>
@endpush
