@extends('layouts.base')

@section('content')
{{-- Navbar mengikuti gaya template aktif --}}
<nav class="bg-secondary sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-white">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-sm text-white/80">
            <a href="/" class="hover:opacity-75">Beranda</a>
            @if($tenant->posts()->published()->exists())
                <a href="/blog" class="hover:opacity-75">Blog</a>
            @endif
            @foreach($navPages as $navPage)
                <a href="/p/{{ $navPage->slug }}" class="hover:opacity-75">{{ $navPage->title }}</a>
            @endforeach
            <a href="/#kontak" class="hover:opacity-75">Kontak</a>
        </div>
        <a href="/#kontak" class="btn-primary text-white px-5 py-2 rounded-full font-medium text-sm">Hubungi Kami</a>
    </div>
</nav>

{{-- Hero halaman custom --}}
<section class="relative text-white">
    @if($page->cover_image)
        <img src="{{ asset($page->cover_image) }}" alt="{{ $page->title }}" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-black/70 to-secondary/70"></div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-primary/80 to-secondary/80"></div>
    @endif
    <div class="relative max-w-7xl mx-auto px-4 py-20 text-center">
        <h1 class="text-3xl md:text-5xl font-extrabold">{{ $page->title }}</h1>
    </div>
</section>

{{-- Konten halaman custom --}}
<section class="py-16">
    <div class="max-w-4xl mx-auto px-4">
        <article class="bg-white rounded-2xl shadow-sm p-8 max-w-none">
            <div class="rich-content">
                {!! rich_content($page->content) !!}
            </div>
        </article>
    </div>
</section>

@include('templates.partials.rich-styles')

<footer class="bg-gray-900 text-gray-400 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
