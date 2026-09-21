@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-white sticky top-0 z-50 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-5 text-sm text-gray-600">
            <a href="#beranda" class="hover:text-primary">Beranda</a>
            <a href="#tentang" class="hover:text-primary">Tentang</a>
            <a href="#kamar" class="hover:text-primary">Kamar</a>
            <a href="#kontak" class="hover:text-primary">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="btn-primary text-white px-5 py-2 rounded-lg text-sm font-medium">Pesan</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="bg-gradient-to-br from-primary to-secondary text-white">
    <div class="max-w-7xl mx-auto px-4 py-36 text-center">
        <p class="uppercase tracking-[0.2em] text-sm opacity-80 mb-4">Penginapan Terjangkau</p>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Nyaman tanpa merogoh kocek dalam' }}</p>
        <a href="#kamar" class="bg-white text-secondary px-8 py-3 rounded-full font-bold">Lihat Tarif</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Tentang Kami</h2>
            <p class="text-gray-600 leading-relaxed mb-6">{{ $profile->description ?? 'Deskripsi penginapan belum diisi.' }}</p>
            <div class="flex gap-4">
                <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-medium">Harga Bersahabat</span>
                <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">Lokasi Strategis</span>
            </div>
        </div>
        <img src="{{
                $profile?->about_image_url ?: static_image('hotel-06', 'full', 'cozy budget hotel room clean simple', 'square')
            }}" alt="Kamar" class="rounded-2xl h-80 w-full object-cover shadow-lg">
    </div>
</section>

{{-- Kamar --}}
<section id="kamar" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Tarif Kamar</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-lg transition text-center">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '🛏️' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-primary font-bold text-2xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                        <p class="text-xs text-gray-400 mt-1">per malam</p>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada kamar.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Kontak --}}
<section id="kontak" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Hubungi & Pesan</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts as $contact)
                <div class="p-6">
                    <div class="text-3xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🌐 @endif
                    </div>
                    <div class="text-gray-500 text-sm mb-1">{{ $contact->label }}</div>
                    <div class="font-semibold text-lg">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-gray-500">Belum ada kontak.</p>
            @endforelse
        </div>
    </div>
</section>

<footer class="bg-gray-900 text-gray-400 py-6 text-center text-sm">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
