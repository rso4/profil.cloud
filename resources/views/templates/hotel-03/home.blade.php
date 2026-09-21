@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-emerald-900/90 backdrop-blur sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-white">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-emerald-50">
            <a href="#beranda" class="hover:text-white">Beranda</a>
            <a href="#tentang" class="hover:text-white">Tentang</a>
            <a href="#villa" class="hover:text-white">Villa</a>
            <a href="#aktivitas" class="hover:text-white">Aktivitas</a>
            <a href="#kontak" class="hover:text-white">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="bg-white text-emerald-700 px-6 py-2.5 rounded-full font-medium">Pesan Villa</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('hotel-03', 'hero', 'tropical resort with palm trees and private villa by the beach, sunset, turquoise water', 'landscape_16_9')
            }}" alt="Resort {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-b from-emerald-900/60 to-black/60"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-44 text-center">
        <p class="uppercase tracking-[0.3em] text-sm mb-4">Resort & Villa</p>
        <h1 class="text-4xl md:text-6xl font-bold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-emerald-50 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Nikmati keindahan alam yang asri' }}</p>
        <a href="#villa" class="bg-white text-emerald-700 px-8 py-3 rounded-full font-semibold">Jelajahi Villa</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <img src="{{
                $profile?->about_image_url ?: static_image('hotel-03', 'full', 'resort pool surrounded by tropical greenery', 'square')
            }}" alt="Tentang" class="rounded-3xl h-96 w-full object-cover shadow-2xl">
        <div>
            <p class="text-emerald-600 font-semibold tracking-wide mb-2">Tentang Kami</p>
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Oase Ketentraman di Tengah Tropis</h2>
            <p class="text-gray-600 leading-relaxed mb-8">{{ $profile->description ?? 'Deskripsi resort belum diisi.' }}</p>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-emerald-600">{{ $services->count() }}+</div>
                    <div class="text-sm text-gray-500">Villa</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-emerald-600">{{ $galleries->count() }}+</div>
                    <div class="text-sm text-gray-500">Spot Foto</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-emerald-600">24/7</div>
                    <div class="text-sm text-gray-500">Layanan</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Villa --}}
<section id="villa" class="py-20 bg-emerald-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Pilihan Villa</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition">
                    <div class="h-44 bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-6xl">
                        {{ $service->icon ?? '🏝️' }}
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                        @if($service->price)
                            <span class="text-emerald-600 font-bold text-2xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada villa.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Aktivitas --}}
<section id="aktivitas" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Aktivitas & Fasilitas</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @forelse($contacts->take(4) as $contact)
                <div class="text-center p-6 rounded-2xl border border-emerald-100 hover:bg-emerald-50 transition">
                    <div class="text-5xl mb-3">
                        @if($contact->type === 'phone') 🏄 @elseif($contact->type === 'email') 🧘 @elseif($contact->type === 'address') 🌴 @else 🍹 @endif
                    </div>
                    <div class="font-medium text-gray-900">{{ $contact->label }}</div>
                </div>
            @empty
                <p class="col-span-4 text-center text-gray-500">Belum ada fasilitas.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Galeri --}}
<section id="galeri" class="py-20 bg-emerald-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Galeri Resort</h2>
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
<section id="kontak" class="py-20 bg-emerald-800 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Reservasi & Kontak</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts as $contact)
                <div class="p-6">
                    <div class="text-4xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🌐 @endif
                    </div>
                    <div class="text-emerald-200 text-sm mb-1">{{ $contact->label }}</div>
                    <div class="font-medium text-lg">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-emerald-200">Belum ada kontak.</p>
            @endforelse
        </div>
    </div>
</section>

<footer class="bg-emerald-900 text-emerald-200 py-8 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
