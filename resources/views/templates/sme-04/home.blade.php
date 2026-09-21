@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-white sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-serif font-bold text-gray-900">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-sm text-gray-600">
            <a href="#beranda" class="hover:text-primary">Beranda</a>
            <a href="#tentang" class="hover:text-primary">Tentang</a>
            <a href="#produk" class="hover:text-primary">Koleksi</a>
            <a href="#galeri" class="hover:text-primary">Galeri</a>
            <a href="#kontak" class="hover:text-primary">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="btn-primary text-white px-6 py-2.5 rounded-full text-sm font-semibold">Belanja</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('sme-04', 'hero', 'fashion boutique with elegant clothing display, stylish store interior', 'landscape_16_9')
            }}" alt="Boutique {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-48 text-center">
        <p class="uppercase tracking-[0.4em] text-sm text-gray-200 mb-4">Fashion & Boutique</p>
        <h1 class="text-4xl md:text-6xl font-serif font-bold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-gray-100 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Gaya terbaik untuk penampilan Anda' }}</p>
        <a href="#produk" class="bg-white text-gray-900 px-8 py-3 rounded-full font-semibold">Lihat Koleksi</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <p class="text-primary font-semibold tracking-wide mb-2">Tentang Kami</p>
            <h2 class="text-3xl md:text-4xl font-serif font-bold mb-6 text-gray-900">Kurasi Fashion untuk Anda</h2>
            <p class="text-gray-600 leading-relaxed mb-6">{{ $profile->description ?? 'Deskripsi butik belum diisi.' }}</p>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">{{ $services->count() }}</div>
                    <div class="text-sm text-gray-500">Koleksi</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">100%</div>
                    <div class="text-sm text-gray-500">Original</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">1000+</div>
                    <div class="text-sm text-gray-500">Pelanggan</div>
                </div>
            </div>
        </div>
        <img src="{{
                $profile?->about_image_url ?: static_image('sme-04', 'full', 'fashion designer workshop with fabric and sketches', 'square')
            }}" alt="Atelier" class="rounded-2xl h-96 w-full object-cover shadow-xl">
    </div>
</section>

{{-- Produk --}}
<section id="produk" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-center mb-12 text-gray-900">Koleksi Kami</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '👗' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-primary font-bold text-2xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada koleksi.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Galeri --}}
<section id="galeri" class="py-20 bg-white">
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
<section id="kontak" class="py-20 bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-center mb-12">Hubungi Kami</h2>
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
