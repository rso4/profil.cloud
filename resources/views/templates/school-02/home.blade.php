@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-white sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-gray-600">
            <a href="#beranda" class="hover:text-primary">Beranda</a>
            <a href="#tentang" class="hover:text-primary">Tentang</a>
            <a href="#program" class="hover:text-primary">Program</a>
            <a href="#galeri" class="hover:text-primary">Galeri</a>
            <a href="#kontak" class="hover:text-primary">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#pendaftaran" class="btn-primary text-white px-6 py-2.5 rounded-lg font-semibold">PPDB</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('school-02', 'hero', 'contemporary school campus with students walking, modern architecture', 'landscape_16_9')
            }}" alt="Sekolah {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/55"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-44">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4 max-w-2xl">{{ $tenant->name }}</h1>
        <p class="text-xl text-gray-200 mb-8 max-w-xl">{{ $profile->tagline ?? 'Modern, inovatif, dan berwawasan global' }}</p>
        <div class="flex gap-4">
            <a href="#program" class="bg-white text-gray-900 px-8 py-3 rounded-lg font-bold">Lihat Program</a>
            <a href="#pendaftaran" class="btn-primary text-white px-8 py-3 rounded-lg font-bold">Daftar</a>
        </div>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-primary font-semibold tracking-wide mb-2">Tentang Kami</p>
        <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Sekolah Modern untuk Generasi Digital</h2>
        <p class="text-gray-600 leading-relaxed max-w-3xl mx-auto mb-12">{{ $profile->description ?? 'Deskripsi sekolah belum diisi.' }}</p>
        <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <div class="p-6">
                <div class="text-5xl mb-3">💻</div>
                <h3 class="font-semibold text-gray-900">Teknologi</h3>
                <p class="text-sm text-gray-500 mt-2">Laboratorium komputer modern</p>
            </div>
            <div class="p-6">
                <div class="text-5xl mb-3">🌍</div>
                <h3 class="font-semibold text-gray-900">Global</h3>
                <p class="text-sm text-gray-500 mt-2">Kurikulum internasional</p>
            </div>
            <div class="p-6">
                <div class="text-5xl mb-3">🎨</div>
                <h3 class="font-semibold text-gray-900">Kreatif</h3>
                <p class="text-sm text-gray-500 mt-2">Pengembangan bakat siswa</p>
            </div>
        </div>
    </div>
</section>

{{-- Program --}}
<section id="program" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Program Pembelajaran</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '📖' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-primary font-bold text-lg">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada program.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Galeri --}}
<section id="galeri" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Galeri</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse($galleries as $gallery)
                <img src="{{ $gallery->thumb_url }}" alt="{{ $gallery->title }}" data-gallery data-fullscreen="{{ $gallery->image_url }}" class="w-full h-52 object-cover rounded-2xl hover:scale-105 transition duration-300">
            @empty
                <p class="col-span-4 text-center text-gray-500">Belum ada galeri.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Kontak --}}
<section id="kontak" class="py-20 bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Hubungi Kami</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts as $contact)
                <div class="p-6">
                    <div class="text-3xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🌐 @endif
                    </div>
                    <div class="text-gray-400 text-sm mb-1">{{ $contact->label }}</div>
                    <div class="font-medium text-lg">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-gray-400">Belum ada kontak.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Pendaftaran --}}
<section id="pendaftaran" class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4 text-gray-900">Penerimaan Peserta Didik Baru</h2>
        <p class="text-gray-600 mb-8">Bergabunglah dengan sekolah modern kami.</p>
        <a href="#kontak" class="bg-primary text-white px-8 py-3 rounded-lg font-bold">Info PPDB</a>
    </div>
</section>

<footer class="bg-gray-950 text-gray-500 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
