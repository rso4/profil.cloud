@extends('admin.layout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Dashboard</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <a href="{{ route('tenant.posts') }}" class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="text-3xl font-bold text-primary">{{ $stats['published'] }}</div>
        <div class="text-gray-500 text-sm">Artikel Terbit</div>
        <div class="text-xs text-gray-400 mt-1">{{ $stats['drafts'] }} draf menunggu</div>
    </a>
    <a href="{{ route('tenant.pages') }}" class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="text-3xl font-bold text-primary">{{ $stats['pages'] }}</div>
        <div class="text-gray-500 text-sm">Halaman Custom</div>
    </a>
    <a href="{{ route('tenant.media') }}" class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="text-3xl font-bold text-primary">{{ $stats['media'] }}</div>
        <div class="text-gray-500 text-sm">Media</div>
    </a>
    <a href="{{ route('tenant.categories') }}" class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="text-3xl font-bold text-primary">{{ $stats['categories'] }}</div>
        <div class="text-gray-500 text-sm">Kategori</div>
    </a>
    <a href="{{ route('tenant.services') }}" class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="text-3xl font-bold text-primary">{{ $stats['services'] }}</div>
        <div class="text-gray-500 text-sm">Layanan</div>
    </a>
    <a href="{{ route('tenant.galleries') }}" class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="text-3xl font-bold text-primary">{{ $stats['galleries'] }}</div>
        <div class="text-gray-500 text-sm">Galeri</div>
    </a>
    <a href="{{ route('tenant.contacts') }}" class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="text-3xl font-bold text-primary">{{ $stats['contacts'] }}</div>
        <div class="text-gray-500 text-sm">Kontak</div>
    </a>
    <a href="/" target="_blank" class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="text-3xl font-bold text-primary">&rarr;</div>
        <div class="text-gray-500 text-sm">Lihat Website</div>
    </a>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Artikel Terbaru</h2>
        <a href="{{ route('tenant.posts.create') }}" class="bg-primary text-white text-sm px-4 py-2 rounded-lg font-medium">+ Tulis Artikel</a>
    </div>
    @if($recentPosts->isEmpty())
        <p class="text-gray-500 text-sm py-4 text-center">
            Belum ada artikel. <a href="{{ route('tenant.posts.create') }}" class="text-primary hover:underline">Tulis artikel pertama Anda</a> untuk mulai membangun blog.
        </p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2">Judul</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">Dilihat</th>
                        <th class="py-2">Tanggal</th>
                        <th class="py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPosts as $post)
                        <tr class="border-b">
                            <td class="py-3 font-medium">{{ Str::limit($post->title, 60) }} @if($post->is_featured)<span title="Unggulan">&#11088;</span>@endif</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : ($post->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                                    {{ $post->statusLabel() }}
                                </span>
                            </td>
                            <td class="py-3 text-gray-500">{{ number_format($post->views) }}</td>
                            <td class="py-3 text-gray-500">{{ ($post->published_at ?? $post->created_at)->format('d/m/Y') }}</td>
                            <td class="py-3"><a href="{{ route('tenant.posts.edit', $post->id) }}" class="text-primary hover:underline">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <a href="{{ route('tenant.posts') }}" class="inline-block text-sm text-primary hover:underline mt-3">Lihat semua artikel &rarr;</a>
    @endif
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Informasi Tenant</h2>
    <div class="grid md:grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-500">Nama:</span> {{ $tenant->name }}</div>
        <div><span class="text-gray-500">Subdomain:</span> {{ $tenant->slug }}.{{ config('app.base_domain') }}</div>
        <div><span class="text-gray-500">Template:</span> {{ $tenant->template_slug }}</div>
        <div><span class="text-gray-500">Status:</span> {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}</div>
        <div><span class="text-gray-500">Warna Primer:</span> <span class="inline-block w-4 h-4 rounded-full align-middle" style="background: {{ $tenant->primary_color }}"></span> {{ $tenant->primary_color }}</div>
        <div><span class="text-gray-500">Warna Sekunder:</span> <span class="inline-block w-4 h-4 rounded-full align-middle" style="background: {{ $tenant->secondary_color }}"></span> {{ $tenant->secondary_color }}</div>
    </div>
</div>
@endsection
