@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-primary">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-gray-600">
            <a href="#beranda" class="hover:text-primary">Beranda</a>
            <a href="#tentang" class="hover:text-primary">Tentang</a>
            <a href="#produk" class="hover:text-primary">Produk</a>
            <a href="#kontak" class="hover:text-primary">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="btn-primary text-white px-5 py-2 rounded-full font-medium">Hubungi Kami</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{ $profile->cover_image_url ?: static_image('sme-01', 'hero', 'creative small business homepage with vibrant gradient hero and product showcase', 'landscape_16_9') }}" alt="{{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-br from-primary/80 to-secondary/70"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-44 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4">{{ $tenant->name }}</h1>
        <p class="text-xl text-gray-100 mb-8">{{ $profile->tagline ?? 'Solusi terbaik untuk kebutuhan Anda' }}</p>
        <a href="#produk" class="bg-white text-secondary px-8 py-3 rounded-full font-semibold">Lihat Produk</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-20">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <img src="{{ $profile->about_image_url ?: static_image('sme-01', 'full', 'creative workshop small business products display', 'square') }}" alt="Suasana {{ $tenant->name }}" class="rounded-2xl h-96 w-full object-cover shadow-xl">
        <div>
            <h2 class="text-3xl font-bold mb-6">Tentang Kami</h2>
            <p class="text-lg text-gray-600 leading-relaxed">{{ $profile->description ?? 'Deskripsi bisnis belum diisi.' }}</p>
        </div>
    </div>
</section>

{{-- Produk/Layanan --}}
<section id="produk" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Produk & Layanan</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="border-2 border-gray-100 rounded-2xl p-6 hover:border-primary transition">
                    <div class="text-4xl mb-4">{{ $service->icon ?? '🛍️' }}</div>
                    <h3 class="text-xl font-semibold mb-2">{{ $service->name }}</h3>
                    <p class="text-gray-600 mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-primary font-bold text-lg">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada produk.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Galeri --}}
<section id="galeri" class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Galeri</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse($galleries as $gallery)
                <img src="{{ $gallery->thumb_url }}" alt="{{ $gallery->title }}" data-gallery data-fullscreen="{{ $gallery->image_url }}" class="w-full h-48 object-cover rounded-xl">
            @empty
                <p class="col-span-4 text-center text-gray-500">Belum ada galeri.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Kontak --}}
<section id="kontak" class="py-20 bg-secondary text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Hubungi Kami</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts as $contact)
                <div>
                    <div class="text-3xl mb-2">
                        @if($contact->type === 'phone') 📞
                        @elseif($contact->type === 'email') ✉️
                        @elseif($contact->type === 'address') 📍
                        @else 🌐
                        @endif
                    </div>
                    <div class="text-gray-300 text-sm">{{ $contact->label }}</div>
                    <div class="font-medium">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-gray-300">Belum ada kontak.</p>
            @endforelse
        </div>
    </div>
</section>

<footer class="bg-gray-900 text-gray-400 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
