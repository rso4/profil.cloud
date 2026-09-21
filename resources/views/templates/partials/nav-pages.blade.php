{{-- Link Blog tenant (muncul jika ada artikel yang sudah terbit) --}}
@if(isset($tenant) && $tenant->posts()->published()->exists())
    <a href="/blog" class="hover:opacity-75">Blog</a>
@endif
{{-- Link halaman custom tenant (muncul hanya jika ada halaman yang ditampilkan di navigasi) --}}
@if(isset($tenant) && $tenant->relationLoaded('pages') ? $tenant->pages->where('show_in_nav', true)->where('is_published', true)->isNotEmpty() : $tenant->pages()->where('show_in_nav', true)->where('is_published', true)->exists())
@foreach(($tenant->relationLoaded('pages') ? $tenant->pages : $tenant->pages()->get())->where('show_in_nav', true)->where('is_published', true)->sortBy('sort_order') as $navPage)
    <a href="/p/{{ $navPage->slug }}" class="hover:opacity-75">{{ $navPage->title }}</a>
@endforeach
@endif