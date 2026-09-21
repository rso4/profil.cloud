@extends('layouts.base')

@section('content')
@include('templates.partials.blog-nav')

{{-- Hero artikel --}}
<section class="relative text-white">
    @if($post->cover_url)
        <img src="{{ $post->cover_url }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-black/70 to-secondary/70"></div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-primary/80 to-secondary/80"></div>
    @endif
    <div class="relative max-w-4xl mx-auto px-4 py-20">
        <a href="/blog" class="text-sm text-white/80 hover:text-white">&larr; Kembali ke Blog</a>
        <h1 class="text-3xl md:text-5xl font-extrabold mt-3">{{ $post->title }}</h1>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-4 text-sm text-white/80">
            @if($post->author)
                <span>&#9998; {{ $post->author->name }}</span>
            @endif
            <span>&#128197; {{ $post->published_at->format('d/m/Y H:i') }}</span>
            <span>&#9203; {{ $post->reading_minutes }} menit baca</span>
            <span>&#128065; {{ number_format($post->views) }} dilihat</span>
        </div>
    </div>
</section>

{{-- Konten artikel --}}
<section class="py-12">
    <div class="max-w-4xl mx-auto px-4">
        <article class="bg-white rounded-2xl shadow-sm p-6 md:p-10">
            <div class="rich-content">
                {!! rich_content($post->content) !!}
            </div>

            @if($post->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2 mt-8 pt-6 border-t border-gray-100">
                    @foreach($post->tags as $tag)
                        <a href="/blog/tag/{{ $tag->slug }}" class="text-xs bg-gray-100 text-gray-600 hover:bg-primary hover:text-white px-3 py-1.5 rounded-full transition">#{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
        </article>

        {{-- Artikel terkait --}}
        @if($relatedPosts->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-xl font-bold mb-4">Artikel Terkait</h2>
                <div class="grid md:grid-cols-3 gap-5">
                    @foreach($relatedPosts as $related)
                        <a href="/blog/{{ $related->slug }}" class="bg-white rounded-2xl shadow-sm overflow-hidden block hover:shadow-lg transition">
                            @if($related->cover_url)
                                <img src="{{ $related->cover_url }}" alt="{{ $related->title }}" class="w-full h-32 object-cover" loading="lazy">
                            @else
                                <div class="w-full h-32 bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center text-3xl">&#128221;</div>
                            @endif
                            <div class="p-4">
                                <p class="font-semibold text-gray-800 line-clamp-2">{{ $related->title }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $related->published_at->format('d/m/Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

@include('templates.partials.rich-styles')

<footer class="bg-gray-900 text-gray-400 py-6 text-center">
    <p>&copy; {{ date('Y') }} {{ $tenant->name }}. Powered by Profil Cloud.</p>
</footer>
@endsection
