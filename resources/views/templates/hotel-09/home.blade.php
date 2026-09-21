@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-white sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-gray-600">
            <a href="#beranda" class="hover:text-primary">Beranda</a>
            <a href="#tentang" class="hover:text-primary">Tentang</a>
            <a href="#kamar" class="hover:text-primary">Kamar</a>
            <a href="#lokasi" class="hover:text-primary">Lokasi</a>
            <a href="#kontak" class="hover:text-primary">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#kontak" class="btn-primary text-white px-6 py-2.5 rounded-lg font-medium">Pesan</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('hotel-09', 'hero', 'modern city hotel with city skyline view, sleek minimalist design', 'landscape_16_9')
            }}" alt="Hotel {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-40">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4 max-w-2xl">{{ $tenant->name }}</h1>
        <p class="text-xl text-gray-200 mb-8 max-w-xl">{{ $profile->tagline ?? 'Hotel di jantung kota, dekat semuanya' }}</p>
        <div class="flex gap-4">
            <a href="#kamar" class="bg-white text-gray-900 px-8 py-3 rounded-lg font-bold">Lihat Kamar</a>
            <a href="#lokasi" class="btn-primary text-white px-8 py-3 rounded-lg font-bold">Cek Lokasi</a>
        </div>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-8 text-gray-900">Tentang Kami</h2>
        <p class="text-gray-600 leading-relaxed max-w-3xl mx-auto mb-12">{{ $profile->description ?? 'Deskripsi hotel belum diisi.' }}</p>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services->take(3) as $service)
                <div class="p-6 bg-gray-50 rounded-2xl">
                    <div class="text-5xl mb-4">{{ $service->icon ?? '🏨' }}</div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-500 text-sm mt-2">{{ $service->description }}</p>
                </div>
            @empty
                <p class="col-span-3 text-gray-500">Belum ada layanan.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Kamar --}}
<section id="kamar" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Kamar Kami</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition">
                    <div class="h-36 bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center text-5xl">
                        {{ $service->icon ?? '🛏️' }}
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                        @if($service->price)
                            <span class="text-primary font-bold text-xl">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada kamar.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Lokasi --}}
<section id="lokasi" class="py-20 bg-primary text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Lokasi Strategis</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts->take(3) as $contact)
                <div class="p-6 bg-white/10 rounded-2xl">
                    <div class="text-3xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🌐 @endif
                    </div>
                    <div class="text-white/80 text-sm mb-1">{{ $contact->label }}</div>
                    <div class="font-medium text-lg">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-white/80">Belum ada info.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Kontak --}}
<section id="kontak" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-8 text-gray-900">Hubungi Kami</h2>
        <p class="text-gray-500 mb-8">Silakan hubungi kami untuk reservasi dan pertanyaan.</p>
        <a href="mailto:{{ $contacts->firstWhere('type','email')->value ?? '#' }}" class="btn-primary text-white px-8 py-3 rounded-lg font-bold inline-block">Kirim Email</a>
    </div>
</section>

<footer class="bg-gray-900 text-gray-400 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
