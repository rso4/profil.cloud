@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-red-700 sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-white">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-red-50">
            <a href="#beranda" class="hover:text-white">Beranda</a>
            <a href="#tentang" class="hover:text-white">Tentang</a>
            <a href="#menu" class="hover:text-white">Menu</a>
            <a href="#galeri" class="hover:text-white">Galeri</a>
            <a href="#kontak" class="hover:text-white">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="bg-white text-red-700 px-6 py-2.5 rounded-full font-semibold">Pesan</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('sme-03', 'hero', 'delicious indonesian culinary dish on restaurant table, appetizing food photography', 'landscape_16_9')
            }}" alt="Kuliner {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-red-900/60"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-44 text-center">
        <p class="uppercase tracking-[0.3em] text-sm mb-4">Restoran & Kuliner</p>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-red-50 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Nikmati cita rasa terbaik kami' }}</p>
        <a href="#menu" class="bg-white text-red-700 px-8 py-3 rounded-full font-bold">Lihat Menu</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-red-600 font-semibold tracking-wide mb-2">Tentang Kami</p>
        <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Rasa Autentik, Bahan Pilihan</h2>
        <p class="text-gray-600 leading-relaxed max-w-3xl mx-auto mb-12">{{ $profile->description ?? 'Deskripsi restoran belum diisi.' }}</p>
        <div class="grid md:grid-cols-3 gap-8 max-w-3xl mx-auto">
            <div>
                <div class="text-5xl mb-3">🔥</div>
                <div class="font-semibold text-gray-900">Dimasak Segar</div>
            </div>
            <div>
                <div class="text-5xl mb-3">🌶️</div>
                <div class="font-semibold text-gray-900">Rasa Pedas Asli</div>
            </div>
            <div>
                <div class="text-5xl mb-3">🥗</div>
                <div class="font-semibold text-gray-900">Bahan Berkualitas</div>
            </div>
        </div>
    </div>
</section>

{{-- Menu --}}
<section id="menu" class="py-20 bg-red-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Menu Andalan</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '🍽️' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-red-600 font-bold text-2xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada menu.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Galeri --}}
<section id="galeri" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Galeri Kuliner</h2>
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
<section id="kontak" class="py-20 bg-red-700 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Reservasi & Kontak</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts as $contact)
                <div class="p-6">
                    <div class="text-3xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🌐 @endif
                    </div>
                    <div class="text-red-100 text-sm mb-1">{{ $contact->label }}</div>
                    <div class="font-medium text-lg">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-red-100">Belum ada kontak.</p>
            @endforelse
        </div>
    </div>
</section>

<footer class="bg-red-900 text-red-100 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
