@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-slate-950/90 backdrop-blur sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-serif font-bold text-white tracking-wide">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-slate-200">
            <a href="#beranda" class="hover:text-white">Beranda</a>
            <a href="#tentang" class="hover:text-white">Tentang</a>
            <a href="#suite" class="hover:text-white">Suite</a>
            <a href="#pengalaman" class="hover:text-white">Pengalaman</a>
            <a href="#kontak" class="hover:text-white">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="btn-primary text-white px-6 py-2.5 rounded-full font-medium">Reservasi</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile->cover_image_url ?: static_image('hotel-10', 'hero', 'premium resort villa with infinity pool overlooking ocean at dusk', 'landscape_16_9')
            }}" alt="Resort {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-black/30"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-52 text-center">
        <p class="uppercase tracking-[0.4em] text-sm text-slate-300 mb-4">Luxury Boutique Resort</p>
        <h1 class="text-4xl md:text-7xl font-serif font-bold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-slate-100 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Kemewahan kontemporer di tepi laut' }}</p>
        <div class="flex justify-center gap-4">
            <a href="#suite" class="bg-white text-slate-900 px-8 py-3 rounded-full font-semibold">Jelajahi Suite</a>
            <a href="#kontak" class="btn-primary text-white px-8 py-3 rounded-full font-semibold">Pesan Sekarang</a>
        </div>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <img src="{{
                $profile->about_image_url ?: static_image('hotel-10', 'full', 'villa interior contemporary luxury ocean view', 'square')
            }}" alt="Villa" class="rounded-3xl h-96 w-full object-cover shadow-2xl">
        <div>
            <p class="text-primary font-semibold tracking-wide mb-2">Tentang Kami</p>
            <h2 class="text-3xl md:text-4xl font-serif font-bold mb-6 text-gray-900">Resort Premium dengan Gaya Kontemporer</h2>
            <p class="text-gray-600 leading-relaxed mb-6">{{ $profile->description ?? 'Deskripsi resort belum diisi.' }}</p>
            <div class="flex gap-8">
                <div>
                    <div class="text-3xl font-bold text-primary">{{ $services->count() }}</div>
                    <div class="text-sm text-gray-500">Suite</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-primary">{{ $galleries->count() }}</div>
                    <div class="text-sm text-gray-500">Galeri</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Suite --}}
<section id="suite" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-center mb-12 text-gray-900">Suite & Villa</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition">
                    <div class="h-40 bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-6xl">
                        {{ $service->icon ?? '🏖️' }}
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                        @if($service->price)
                            <span class="text-primary font-bold text-2xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                            <span class="text-gray-400 text-sm">/malam</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada suite.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Pengalaman --}}
<section id="pengalaman" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-center mb-12 text-gray-900">Pengalaman Resort</h2>
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
<section id="kontak" class="py-20 bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-center mb-12">Reservasi & Kontak</h2>
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

<footer class="bg-slate-950 text-slate-500 py-8 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
