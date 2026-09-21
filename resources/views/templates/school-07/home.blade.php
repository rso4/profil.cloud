@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-slate-800 sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-white">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-slate-200">
            <a href="#beranda" class="hover:text-white">Beranda</a>
            <a href="#tentang" class="hover:text-white">Tentang</a>
            <a href="#jurusan" class="hover:text-white">Jurusan</a>
            <a href="#galeri" class="hover:text-white">Galeri</a>
            <a href="#kontak" class="hover:text-white">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#pendaftaran" class="bg-orange-500 text-white px-6 py-2.5 rounded-lg font-bold">PPDB</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile->cover_image_url ?: static_image('school-07', 'hero', 'vocational school students in workshop practicing engineering skills', 'landscape_16_9')
            }}" alt="Sekolah {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-slate-900/60"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-48 text-center">
        <p class="uppercase tracking-[0.3em] text-sm mb-4">SMK / Sekolah Kejuruan</p>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-slate-200 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Terampil, profesional, siap kerja' }}</p>
        <a href="#jurusan" class="bg-orange-500 text-white px-8 py-3 rounded-lg font-bold">Lihat Jurusan</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <img src="{{
                $profile->about_image_url ?: static_image('school-07', 'full', 'vocational training workshop with modern equipment', 'square')
            }}" alt="Bengkel" class="rounded-2xl h-80 w-full object-cover shadow-lg">
        <div>
            <p class="text-orange-600 font-semibold tracking-wide mb-2">Tentang Kami</p>
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Pendidikan Vokasi Berbasis Kompetensi</h2>
            <p class="text-gray-600 leading-relaxed mb-6">{{ $profile->description ?? 'Deskripsi sekolah belum diisi.' }}</p>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">90%</div>
                    <div class="text-sm text-gray-500">Lulusan Terserap</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">{{ $services->count() }}</div>
                    <div class="text-sm text-gray-500">Jurusan</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">50+</div>
                    <div class="text-sm text-gray-500">Mitra Industri</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Jurusan --}}
<section id="jurusan" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Jurusan Unggulan</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:border-orange-500 hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '🔧' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-orange-600 font-bold text-lg">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada jurusan.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Galeri --}}
<section id="galeri" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Galeri Praktik</h2>
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
<section id="kontak" class="py-20 bg-slate-800 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Hubungi Kami</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts as $contact)
                <div class="p-6">
                    <div class="text-3xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🌐 @endif
                    </div>
                    <div class="text-slate-400 text-sm mb-1">{{ $contact->label }}</div>
                    <div class="font-medium text-lg">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-slate-400">Belum ada kontak.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Pendaftaran --}}
<section id="pendaftaran" class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4 text-gray-900">Penerimaan Peserta Didik Baru</h2>
        <p class="text-gray-600 mb-8">Raih skill dan masa depan cerah bersama kami.</p>
        <a href="#kontak" class="bg-orange-500 text-white px-8 py-3 rounded-lg font-bold">Info PPDB</a>
    </div>
</section>

<footer class="bg-slate-900 text-slate-500 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
