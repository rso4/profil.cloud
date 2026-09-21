@extends('layouts.base')

@section('content')
{{-- Navbar --}}
<nav class="bg-pink-500 sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-white">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-pink-50">
            <a href="#beranda" class="hover:text-white">Beranda</a>
            <a href="#tentang" class="hover:text-white">Tentang</a>
            <a href="#program" class="hover:text-white">Program</a>
            <a href="#galeri" class="hover:text-white">Galeri</a>
            <a href="#kontak" class="hover:text-white">Kontak</a>
            @include('templates.partials.nav-pages')
        </div>
        <a href="#pendaftaran" class="bg-white text-pink-600 px-6 py-2.5 rounded-full font-bold">Daftar</a>
    </div>
</nav>

{{-- Hero --}}
<section id="beranda" class="relative text-white">
    <img src="{{
                $profile?->cover_image_url ?: static_image('school-05', 'hero', 'colorful kindergarten classroom with happy toddlers playing and learning', 'landscape_16_9')
            }}" alt="PAUD {{ $tenant->name }}" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-pink-600/50"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-48 text-center">
        <p class="text-5xl mb-4">🎨🧸🎈</p>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">{{ $tenant->name }}</h1>
        <p class="text-xl text-pink-50 mb-10 max-w-2xl mx-auto">{{ $profile->tagline ?? 'Belajar sambil bermain, tumbuh bersama ceria' }}</p>
        <a href="#tentang" class="bg-white text-pink-600 px-8 py-3 rounded-full font-bold">Jelajahi</a>
    </div>
</section>

{{-- Tentang --}}
<section id="tentang" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-pink-500 font-semibold tracking-wide mb-2">Tentang Kami</p>
        <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900">Tempat Tumbuh Kembang Anak</h2>
        <p class="text-gray-600 leading-relaxed max-w-3xl mx-auto mb-12">{{ $profile->description ?? 'Deskripsi PAUD belum diisi.' }}</p>
        <div class="grid md:grid-cols-4 gap-6 max-w-4xl mx-auto">
            <div>
                <div class="text-5xl mb-3">🎵</div>
                <div class="font-semibold text-gray-900">Musik & Seni</div>
            </div>
            <div>
                <div class="text-5xl mb-3">🧩</div>
                <div class="font-semibold text-gray-900">Belajar Interaktif</div>
            </div>
            <div>
                <div class="text-5xl mb-3">⚽</div>
                <div class="font-semibold text-gray-900">Motorik</div>
            </div>
            <div>
                <div class="text-5xl mb-3">📚</div>
                <div class="font-semibold text-gray-900">Literasi</div>
            </div>
        </div>
    </div>
</section>

{{-- Program --}}
<section id="program" class="py-20 bg-pink-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Program Kegiatan</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="bg-white rounded-3xl p-6 shadow-sm hover:shadow-lg transition text-center">
                    <div class="text-6xl mb-4">{{ $service->icon ?? '🧸' }}</div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ $service->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                    @if($service->price)
                        <span class="text-pink-500 font-bold text-lg">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-500">Belum ada program.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Galeri --}}
<section id="galeri" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900">Galeri Kegiatan Anak</h2>
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
<section id="kontak" class="py-20 bg-pink-600 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Hubungi Kami</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            @forelse($contacts as $contact)
                <div class="p-6">
                    <div class="text-3xl mb-3">
                        @if($contact->type === 'phone') 📞 @elseif($contact->type === 'email') ✉️ @elseif($contact->type === 'address') 📍 @else 🌐 @endif
                    </div>
                    <div class="text-pink-100 text-sm mb-1">{{ $contact->label }}</div>
                    <div class="font-medium text-lg">{{ $contact->value }}</div>
                </div>
            @empty
                <p class="col-span-3 text-pink-100">Belum ada kontak.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Pendaftaran --}}
<section id="pendaftaran" class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4 text-gray-900">Penerimaan Murid Baru</h2>
        <p class="text-gray-600 mb-8">Titipkan buah hati Anda pada tempat yang tepat.</p>
        <a href="#kontak" class="bg-pink-500 text-white px-8 py-3 rounded-full font-bold">Info Pendaftaran</a>
    </div>
</section>

<footer class="bg-pink-800 text-pink-100 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
