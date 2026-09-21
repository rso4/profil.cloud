@extends('admin.layout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Profil Bisnis</h1>

<div class="bg-white rounded-xl p-6 shadow-sm max-w-2xl">
    <form method="POST" action="{{ route('tenant.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Nama Bisnis</label>
            <input type="text" name="name" value="{{ old('name', $tenant->name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
            <p class="text-xs text-gray-500 mt-1">Nama yang tampil di navbar, hero, dan footer website.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Gambar Hero (Beranda)</label>
                @if($profile->cover_image_url)
                    <img src="{{ $profile->cover_image_url }}" alt="Hero saat ini" class="w-full h-32 object-cover rounded-lg mb-2">
                    <p class="text-xs text-gray-500 mb-1">Kosongkan jika tidak ingin mengganti.</p>
                @else
                    <p class="text-xs text-gray-500 mb-1">Belum ada gambar kustom — beranda memakai gambar bawaan template.</p>
                @endif
                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm">
                <p class="text-xs text-gray-500 mt-1">Gambar latar utama di bagian paling atas beranda. Maks 5MB: JPG, PNG, WEBP.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Gambar "Tentang Kami"</label>
                @if($profile->about_image_url)
                    <img src="{{ $profile->about_image_url }}" alt="Tentang saat ini" class="w-full h-32 object-cover rounded-lg mb-2">
                @else
                    <p class="text-xs text-gray-500 mb-1">Belum ada gambar kustom — beranda memakai gambar bawaan template.</p>
                @endif
                <input type="file" name="about_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm">
                <p class="text-xs text-gray-500 mt-1">Gambar di samping teks Tentang Kami. Maks 5MB: JPG, PNG, WEBP.</p>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Tagline</label>
            <input type="text" name="tagline" value="{{ old('tagline', $profile->tagline) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea name="description" rows="5" class="w-full border border-gray-300 rounded-lg px-4 py-2">{{ old('description', $profile->description) }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Website</label>
            <input type="url" name="website" value="{{ old('website', $profile->website) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
        </div>
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Instagram</label>
                <input type="text" name="instagram" value="{{ old('instagram', $profile->instagram) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Facebook</label>
                <input type="text" name="facebook" value="{{ old('facebook', $profile->facebook) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium">Simpan Profil</button>
    </form>
</div>
@endsection
