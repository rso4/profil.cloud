{{-- Navigasi halaman blog publik tenant --}}
<nav class="bg-secondary sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="text-2xl font-bold text-white">{{ $tenant->name }}</a>
        <div class="hidden md:flex space-x-6 text-sm text-white/80">
            <a href="/" class="hover:opacity-75">Beranda</a>
            <a href="/blog" class="hover:opacity-75">Blog</a>
            @foreach($navPages as $navPage)
                <a href="/p/{{ $navPage->slug }}" class="hover:opacity-75">{{ $navPage->title }}</a>
            @endforeach
            <a href="/#kontak" class="hover:opacity-75">Kontak</a>
        </div>
        <a href="/#kontak" class="btn-primary text-white px-5 py-2 rounded-full font-medium text-sm">Hubungi Kami</a>
    </div>
</nav>
