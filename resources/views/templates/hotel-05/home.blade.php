@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-slate-900/90 backdrop-blur sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-white tracking-tight">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-slate-200">
            <a href="#beranda" class="hover:text-white">Beranda</a>
            <a href="#tentang" class="hover:text-white">Tentang</a>
            <a href="#kamar" class="hover:text-white">Kamar</a>
            <a href="#bisnis" class="hover:text-white">Bisnis</a>
            <a href="#kontak" class="hover:text-white">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="bg-white text-slate-900 px-6 py-2.5 rounded-lg font-semibold">Reservasi</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('hotel-05', 'hero', 'modern business hotel with glass tower skyline, corporate feel', 'landscape_16_9')
            }}" alt="Hotel {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-44 text-center">
        <p class="uppercase tracking-[0.25em] text-sm text-slate-200 mb-4">Business Hotel</p>
        <h1 class="text-4xl md:text-6xl font-bold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-slate-200 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Kennyamanan untuk kebutuhan profesional Anda' }}</p>
        <div class="flex justify-center gap-4">
            <a href="#kamar" class="bg-white text-slate-900 px-8 py-3 rounded-lg font-bold">Cek Kamar</a>
            <a href="#kontak" class="btn-primary text-white px-8 py-3 rounded-lg font-bold">Pesan Sekarang</a>
        </div>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <p class="text-primary font-semibold tracking-wide mb-2">Tentang Kami</p>
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Partner Terbaik untuk Perjalanan Bisnis</h2>
            <p class="text-gray-600 leading-relaxed mb-6">{{ $profile->description ?? 'Deskripsi hotel belum diisi.' }}</p>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">2</div>
                    <div class="text-sm text-gray-500">Lokasi</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">{{ $services->count() }}</div>
                    <div class="text-sm text-gray-500">Kamar</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">24h</div>
                    <div class="text-sm text-gray-500">Concierge</div>
                </div>
            </div>
        </div>
        <img src="{{
                $profile?->about_image_url ?: static_image('hotel-05', 'full', 'modern business hotel room with workspace desk', 'square')
            }}" alt="Kamar Bisnis" class="rounded-2xl h-96 w-full object-cover shadow-xl">
    </div>
</section>

{{-- Kamar --}}
<section id="kamar" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Kamar untuk Profesional</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '💼' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-primary font-bold text-xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada kamar.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Bisnis --}}
<section id="bisnis" class="py-20 bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Fasilitas Bisnis</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @forelse($contacts->take(4) as $contact)
                <div class="p-6">
                    <div class="text-4xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🖥️ @endif
                    </div>
                    <div class="font-medium">{{ $contact->label }}</div>
                    <div class="text-sm text-slate-400">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-4 text-center text-slate-400">Belum ada fasilitas.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Kontak --}}
<section id="kontak" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Hubungi Kami</h2>
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

<footer class="bg-slate-900 text-slate-400 py-8 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
