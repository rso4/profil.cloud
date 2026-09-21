@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-slate-900 sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-white">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-slate-200">
            <a href="#beranda" class="hover:text-white">Beranda</a>
            <a href="#tentang" class="hover:text-white">Tentang</a>
            <a href="#layanan" class="hover:text-white">Layanan</a>
            <a href="#galeri" class="hover:text-white">Galeri</a>
            <a href="#kontak" class="hover:text-white">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="bg-white text-slate-900 px-6 py-2.5 rounded-lg font-semibold">Konsultasi</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('sme-07', 'hero', 'professional service business consultant handshake meeting modern office', 'landscape_16_9')
            }}" alt="Jasa {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-slate-900/60"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-44 text-center">
        <p class="uppercase tracking-[0.3em] text-sm text-slate-300 mb-4">Perusahaan Jasa</p>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-slate-100 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Layanan terpercaya untuk solusi terbaik' }}</p>
        <a href="#layanan" class="bg-white text-slate-900 px-8 py-3 rounded-lg font-bold">Lihat Layanan</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <p class="text-primary font-semibold tracking-wide mb-2">Tentang Kami</p>
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Solusi Profesional Setiap Waktu</h2>
            <p class="text-gray-600 leading-relaxed mb-6">{{ $profile->description ?? 'Deskripsi perusahaan jasa belum diisi.' }}</p>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">24/7</div>
                    <div class="text-sm text-gray-500">Dukungan</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">{{ $services->count() }}</div>
                    <div class="text-sm text-gray-500">Layanan</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary">10+</div>
                    <div class="text-sm text-gray-500">Ahli</div>
                </div>
            </div>
        </div>
        <img src="{{
                $profile?->about_image_url ?: static_image('sme-07', 'full', 'professional consultant presenting to client in office', 'square')
            }}" alt="Konsultasi" class="rounded-2xl h-96 w-full object-cover shadow-xl">
    </div>
</section>

{{-- Layanan --}}
<section id="layanan" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Layanan Kami</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:border-primary hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '🛠️' }}</div>
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
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Galeri Proyek</h2>
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
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Hubungi Kami</h2>
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

<footer class="bg-slate-950 text-slate-500 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
