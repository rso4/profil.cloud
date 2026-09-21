@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-amber-500 sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-white">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-amber-50">
            <a href="#beranda" class="hover:text-white">Beranda</a>
            <a href="#prestasi" class="hover:text-white">Prestasi</a>
            <a href="#program" class="hover:text-white">Program</a>
            <a href="#galeri" class="hover:text-white">Galeri</a>
            <a href="#kontak" class="hover:text-white">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#pendaftaran" class="bg-white text-amber-600 px-6 py-2.5 rounded-lg font-bold">PPDB</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile->cover_image_url ?: static_image('school-04', 'hero', 'achievement school students celebrating with trophies, graduation cap, confetti', 'landscape_16_9')
            }}" alt="Sekolah {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-amber-900/60"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-44 text-center">
        <p class="uppercase tracking-[0.3em] text-sm mb-4">Sekolah Berprestasi</p>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-amber-50 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Mencetak juara berkarakter' }}</p>
        <a href="#prestasi" class="bg-white text-amber-600 px-8 py-3 rounded-lg font-bold">Lihat Prestasi</a>
    </div>
</section>

{{-- Prestasi --}}
<section id="prestasi" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-amber-600 font-semibold tracking-wide mb-2">Prestasi Kami</p>
        <h2 class="text-3xl md:text-4xl font-bold mb-12 text-gray-900">Raihan Terbaik Siswa</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services->take(3) as $service)
                <div class="p-6 bg-amber-50 rounded-2xl">
                    <div class="text-5xl mb-4">🏆</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm">{{ $service->description }}</p>
                </div>
            @empty
                <p class="col-span-3 text-gray-500">Belum ada data prestasi.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Program --}}
<section id="program" class="py-20 bg-amber-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Program Unggulan</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '⭐' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-amber-600 font-bold text-lg">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
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
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Galeri Kegiatan</h2>
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
<section id="kontak" class="py-20 bg-amber-600 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Hubungi Kami</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts as $contact)
                <div class="p-6">
                    <div class="text-3xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🌐 @endif
                    </div>
                    <div class="text-amber-100 text-sm mb-1">{{ $contact->label }}</div>
                    <div class="font-medium text-lg">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-amber-100">Belum ada kontak.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Pendaftaran --}}
<section id="pendaftaran" class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4 text-gray-900">Penerimaan Peserta Didik Baru</h2>
        <p class="text-gray-600 mb-8">Bergabunglah dengan keluarga besar kami.</p>
        <a href="#kontak" class="bg-amber-500 text-white px-8 py-3 rounded-lg font-bold">Info PPDB</a>
    </div>
</section>

<footer class="bg-amber-800 text-amber-100 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
