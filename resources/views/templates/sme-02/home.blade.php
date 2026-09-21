@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-white/90 backdrop-blur sticky top-0 z-50 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-gray-900 tracking-tight">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-sm text-gray-600">
            <a href="#beranda" class="hover:text-primary">Beranda</a>
            <a href="#tentang" class="hover:text-primary">Tentang</a>
            <a href="#layanan" class="hover:text-primary">Layanan</a>
            <a href="#galeri" class="hover:text-primary">Galeri</a>
            <a href="#kontak" class="hover:text-primary">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold">Hubungi Kami</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('sme-02', 'hero', 'modern small business office team working together, bright professional workspace', 'landscape_16_9')
            }}" alt="{{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gray-900/60"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-44 text-center">
        <p class="uppercase tracking-[0.3em] text-sm text-gray-200 mb-4">Partner Bisnis Anda</p>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-gray-100 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Solusi profesional untuk kebutuhan bisnis' }}</p>
        <div class="flex justify-center gap-4">
            <a href="#layanan" class="bg-white text-gray-900 px-8 py-3 rounded-lg font-bold">Lihat Layanan</a>
            <a href="#kontak" class="btn-primary text-white px-8 py-3 rounded-lg font-bold">Hubungi</a>
        </div>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <img src="{{
                $profile?->about_image_url ?: static_image('sme-02', 'full', 'professional business team meeting in modern office', 'square')
            }}" alt="Tentang" class="rounded-2xl h-96 w-full object-cover shadow-xl">
        <div>
            <p class="text-primary font-semibold tracking-wide mb-2">Tentang Kami</p>
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Berkembang Bersama Anda</h2>
            <p class="text-gray-600 leading-relaxed mb-6">{{ $profile->description ?? 'Deskripsi bisnis belum diisi.' }}</p>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">500+</div>
                    <div class="text-sm text-gray-500">Klien</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">{{ $services->count() }}</div>
                    <div class="text-sm text-gray-500">Layanan</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">10+</div>
                    <div class="text-sm text-gray-500">Tahun</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Layanan --}}
<section id="layanan" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Layanan Kami</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:border-primary hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '💼' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-primary font-bold text-xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada layanan.</p>
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

<footer class="bg-gray-950 text-gray-500 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
