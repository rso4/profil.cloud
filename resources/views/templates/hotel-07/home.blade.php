@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-amber-900/90 backdrop-blur sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-serif font-bold text-white">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-amber-100">
            <a href="#beranda" class="hover:text-white">Beranda</a>
            <a href="#tentang" class="hover:text-white">Tentang</a>
            <a href="#kamar" class="hover:text-white">Kamar</a>
            <a href="#galeri" class="hover:text-white">Galeri</a>
            <a href="#kontak" class="hover:text-white">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="bg-white text-amber-800 px-6 py-2.5 rounded-lg font-medium">Reservasi</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile->cover_image_url ?: static_image('hotel-07', 'hero', 'heritage hotel colonial architecture, classic facade, timeless elegance', 'landscape_16_9')
            }}" alt="Hotel {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-t from-amber-900/80 to-black/40"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-48 text-center">
        <p class="uppercase tracking-[0.3em] text-sm mb-4">Heritage Experience</p>
        <h1 class="text-4xl md:text-6xl font-serif font-bold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-amber-50 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Kisah sejarah di setiap ruang' }}</p>
        <a href="#tentang" class="bg-white text-amber-800 px-8 py-3 rounded-lg font-semibold">Jelajahi Sejarah</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-24 bg-amber-50">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <p class="text-amber-700 font-semibold tracking-wide mb-2">Warisan Budaya</p>
            <h2 class="text-3xl md:text-4xl font-serif font-bold mb-6 text-gray-900">Bangunan Bersejarah dengan Kenyamanan Modern</h2>
            <p class="text-gray-600 leading-relaxed mb-6">{{ $profile->description ?? 'Deskripsi hotel belum diisi.' }}</p>
            <p class="text-gray-500 leading-relaxed">Dibangun sejak zaman kolonial, hotel kami memadukan arsitektur klasik dengan fasilitas kontemporer.</p>
        </div>
        <img src="{{
                $profile->about_image_url ?: static_image('hotel-07', 'full', 'hotel grand staircase heritage interior classic', 'square')
            }}" alt="Interior" class="rounded-2xl h-96 w-full object-cover shadow-xl">
    </div>
</section>

{{-- Kamar --}}
<section id="kamar" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-center mb-12 text-gray-900">Kamar Bersejarah</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-amber-50 rounded-2xl p-6 hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '🏰' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-amber-700 font-bold text-xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada kamar.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Galeri --}}
<section id="galeri" class="py-20 bg-amber-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-center mb-12 text-gray-900">Galeri</h2>
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
<section id="kontak" class="py-20 bg-amber-800 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-center mb-12">Hubungi Kami</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts as $contact)
                <div class="p-6">
                    <div class="text-3xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🌐 @endif
                    </div>
                    <div class="text-amber-200 text-sm mb-1">{{ $contact->label }}</div>
                    <div class="font-medium text-lg">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-amber-200">Belum ada kontak.</p>
            @endforelse
        </div>
    </div>
</section>

<footer class="bg-amber-900 text-amber-200 py-8 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
