@extends('layouts.base')

@section('content')
@include('templates.partials.blog-nav')

{{-- Header blog --}}
<section class="relative text-white">
    <div class="absolute inset-0 bg-gradient-to-br from-primary/80 to-secondary/80"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-16 text-center">
        <p class="text-xs tracking-widest uppercase text-white/60">Blog</p>
        <h1 class="text-3xl md:text-5xl font-extrabold mt-2">
            @if(isset($category))
                {{ $category->name }}
            @elseif(isset($tag))
                #{{ $tag->name }}
            @else
                Blog &amp; Artikel
            @endif
        </h1>
        @if(isset($category) && $category->description)
            <p class="mt-3 text-white/80 max-w-2xl mx-auto">{{ $category->description }}</p>
        @elseif(isset($tag))
            <p class="mt-3 text-white/80">Artikel dengan tag &ldquo;{{ $tag->name }}&rdquo; dari {{ $tenant->name }}.</p>
        @else
            <p class="mt-3 text-white/80">Kabar, artikel, dan informasi terbaru dari {{ $tenant->name }}.</p>
        @endif
    </div>
</section>

{{-- Daftar artikel --}}
<section class="py-14">
    <div class="max-w-7xl mx-auto px-4">
        @if ($posts->count())
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                    <article class="bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition">
                        <a href="/blog/{{ $post->slug }}" class="block">
                            @if($post->cover_url)
                                <img src="{{ $post->cover_url }}" alt="{{ $post->title }}" class="w-full h-48 object-cover" loading="lazy">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center text-4xl">&#128221;</div>
                            @endif
                        </a>
                        <div class="p-5 flex flex-col flex-1">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-400 mb-2">
                                @if($post->category)
                                    <a href="/blog/kategori/{{ $post->category->slug }}" class="text-primary font-semibold hover:underline">{{ $post->category->name }}</a>
                                    <span>&middot;</span>
                                @endif
                                <span>{{ $post->published_at->format('d/m/Y') }}</span>
                                @if($post->is_featured)
                                    <span class="text-amber-500" title="Artikel unggulan">&#9733;</span>
                                @endif
                            </div>
                            <a href="/blog/{{ $post->slug }}" class="text-lg font-bold text-gray-800 hover:text-primary">{{ $post->title }}</a>
                            <p class="text-sm text-gray-500 mt-2 flex-1">{{ $post->excerpt_text }}</p>
                            <div class="flex items-center justify-between mt-4 text-xs text-gray-400">
                                <span>{{ $post->reading_minutes }} menit baca</span>
                                <a href="/blog/{{ $post->slug }}" class="text-primary font-semibold hover:underline">Baca &rarr;</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-10">{{ $posts->links() }}</div>
        @else
            <div class="bg-white rounded-2xl shadow-sm py-16 text-center text-gray-500">
                <p class="text-4xl mb-3">&#128221;</p>
                <p>Belum ada artikel yang dipublikasikan{{ isset($category) || isset($tag) ? ' di sini' : '' }}.</p>
                <a href="/blog" class="inline-block mt-4 text-primary hover:underline">&larr; Lihat semua artikel</a>
            </div>
        @endif
    </div>
</section>

<footer class="bg-gray-900 text-gray-400 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
