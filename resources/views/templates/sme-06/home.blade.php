@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-white sticky top-0 z-50 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-sm text-gray-600">
            <a href="#beranda" class="hover:text-primary">Beranda</a>
            <a href="#tentang" class="hover:text-primary">Tentang</a>
            <a href="#produk" class="hover:text-primary">Produk</a>
            <a href="#galeri" class="hover:text-primary">Galeri</a>
            <a href="#kontak" class="hover:text-primary">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Belanja</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('sme-06', 'hero', 'online store products display, ecommerce packaging boxes and products', 'landscape_16_9')
            }}" alt="Toko {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-blue-900/60"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-44 text-center">
        <p class="uppercase tracking-[0.3em] text-sm mb-4">Toko Online</p>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-blue-50 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Belanja mudah, produk berkualitas' }}</p>
        <a href="#produk" class="bg-white text-blue-900 px-8 py-3 rounded-lg font-bold">Lihat Produk</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <img src="{{
                $profile?->about_image_url ?: static_image('sme-06', 'full', 'warehouse shipping ecommerce orders packing', 'square')
            }}" alt="Pengiriman" class="rounded-2xl h-80 w-full object-cover shadow-lg">
        <div>
            <p class="text-primary font-semibold tracking-wide mb-2">Tentang Kami</p>
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Belanja Mudah, Kirim Cepat</h2>
            <p class="text-gray-600 leading-relaxed mb-6">{{ $profile->description ?? 'Deskripsi toko belum diisi.' }}</p>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">{{ $services->count() }}</div>
                    <div class="text-sm text-gray-500">Produk</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">24h</div>
                    <div class="text-sm text-gray-500">Kirim</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">100%</div>
                    <div class="text-sm text-gray-500">Asli</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Produk --}}
<section id="produk" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Produk Unggulan</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition">
                    <div class="h-36 bg-gradient-to-br from-blue-500 to-primary flex items-center justify-center text-5xl">
                        {{ $service->icon ?? '📦' }}
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                        @if($service->price)
                            <span class="text-primary font-bold text-2xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada produk.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Galeri --}}
<section id="galeri" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Galeri Produk</h2>
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

<footer class="bg-gray-950 text-gray-500 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
