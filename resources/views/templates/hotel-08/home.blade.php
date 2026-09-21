@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-white/90 backdrop-blur sticky top-0 z-50 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-light tracking-wider text-gray-900">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-gray-600">
            <a href="#beranda" class="hover:text-primary">Beranda</a>
            <a href="#tentang" class="hover:text-primary">Tentang</a>
            <a href="#spa" class="hover:text-primary">Spa</a>
            <a href="#kamar" class="hover:text-primary">Kamar</a>
            <a href="#kontak" class="hover:text-primary">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="btn-primary text-white px-6 py-2.5 rounded-full text-sm">Reservasi Spa</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('hotel-08', 'hero', 'luxury spa and wellness hotel, tranquil atmosphere, soft candles and stones', 'landscape_16_9')
            }}" alt="Spa {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-b from-black/50 to-primary/40"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-48 text-center">
        <p class="uppercase tracking-[0.35em] text-sm mb-4">Wellness & Relaxation</p>
        <h1 class="text-4xl md:text-6xl font-light mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Raih ketenangan jiwa dan raga' }}</p>
        <a href="#spa" class="bg-white text-gray-900 px-8 py-3 rounded-full font-medium">Temukan Kedamaian</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-primary tracking-wide uppercase text-sm font-medium mb-2">Tentang Kami</p>
        <h2 class="text-3xl md:text-4xl font-light mb-8 text-gray-900">Refleksi dan Pemulihan</h2>
        <p class="text-gray-600 leading-relaxed max-w-3xl mx-auto mb-12">{{ $profile->description ?? 'Deskripsi spa belum diisi.' }}</p>
        <div class="grid grid-cols-3 gap-8 max-w-3xl mx-auto">
            <div>
                <div class="text-4xl mb-2">🧖‍♀️</div>
                <div class="font-semibold text-gray-900">Terapis Ahli</div>
            </div>
            <div>
                <div class="text-4xl mb-2">🌿</div>
                <div class="font-semibold text-gray-900">Produk Alami</div>
            </div>
            <div>
                <div class="text-4xl mb-2">🕯️</div>
                <div class="font-semibold text-gray-900">Suasana Tenang</div>
            </div>
        </div>
    </div>
</section>

{{-- Spa --}}
<section id="spa" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-light text-center mb-12 text-gray-900">Program & Perawatan</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-3xl p-8 text-center shadow-sm hover:shadow-lg transition">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '💆' }}</div>
                    <h3 class="text-xl font-medium mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-primary font-semibold text-2xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada program spa.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Kamar --}}
<section id="kamar" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-light text-center mb-12 text-gray-900">Kamar Relaksasi</h2>
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
        <h2 class="text-3xl md:text-4xl font-light text-center mb-12">Hubungi Kami</h2>
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

<footer class="bg-gray-950 text-gray-500 py-8 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
