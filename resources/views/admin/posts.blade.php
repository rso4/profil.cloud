@extends('admin.layout')

@section('title', 'Artikel')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-bold">Artikel</h1>
    <div class="flex items-center gap-4">
        <a href="{{ route('tenant.posts.trash') }}" class="text-sm text-gray-500 hover:text-gray-700">🗑️ Sampah ({{ $counts['trash'] }})</a>
        <a href="{{ route('tenant.posts.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium">+ Tulis Artikel</a>
    </div>
</div>

{{-- Filter status & pencarian --}}
<div class="bg-white rounded-xl p-4 shadow-sm mb-6">
    <form method="GET" action="{{ route('tenant.posts') }}" class="flex flex-wrap items-center gap-3">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari judul artikel..." class="border border-gray-300 rounded-lg px-4 py-2 text-sm flex-1 min-w-[200px]">
        @if($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <button type="submit" class="bg-secondary text-white px-5 py-2 rounded-lg text-sm">Cari</button>
    </form>
    <div class="flex flex-wrap gap-2 mt-3 text-sm">
        @php
            $statusTabs = [
                '' => ['Semua', $counts['all']],
                \App\Models\TenantPost::STATUS_PUBLISHED => ['Terbit', $counts['published']],
                \App\Models\TenantPost::STATUS_SCHEDULED => ['Terjadwal', $counts['scheduled']],
                \App\Models\TenantPost::STATUS_DRAFT => ['Draf', $counts['draft']],
            ];
        @endphp
        @foreach($statusTabs as $value => [$label, $count])
            <a href="{{ route('tenant.posts', array_filter(['status' => $value, 'q' => $q])) }}"
                class="px-3 py-1.5 rounded-full border {{ ($status ?? '') === $value ? 'bg-primary text-white border-primary' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                {{ $label }} ({{ $count }})
            </a>
        @endforeach
    </div>
</div>

{{-- Daftar artikel --}}
<div class="bg-white rounded-xl p-6 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">Cover</th>
                    <th class="py-2">Judul</th>
                    <th class="py-2">Kategori</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Dilihat</th>
                    <th class="py-2">Tanggal</th>
                    <th class="py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr class="border-b">
                        <td class="py-3">
                            @if($post->cover_url)
                                <img src="{{ $post->cover_url }}" alt="{{ $post->title }}" class="w-14 h-10 object-cover rounded" loading="lazy">
                            @else
                                <div class="w-14 h-10 bg-gray-100 rounded flex items-center justify-center text-gray-300">—</div>
                            @endif
                        </td>
                        <td class="py-3">
                            <a href="{{ route('tenant.posts.edit', $post->id) }}" class="font-medium text-gray-800 hover:text-primary">{{ $post->title }}</a>
                            @if($post->is_featured)<span class="text-amber-500" title="Artikel unggulan">&#9733;</span>@endif
                            <p class="text-xs text-gray-400">/blog/{{ $post->slug }}</p>
                        </td>
                        <td class="py-3">{{ $post->category?->name ?? '—' }}</td>
                        <td class="py-3">
                            @php
                                $badge = match ($post->status) {
                                    'published' => ['Terbit', 'bg-green-100 text-green-700'],
                                    'scheduled' => ['Terjadwal', 'bg-amber-100 text-amber-700'],
                                    default => ['Draf', 'bg-gray-100 text-gray-500'],
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $badge[1] }}">{{ $badge[0] }}</span>
                        </td>
                        <td class="py-3 text-gray-500">{{ number_format($post->views) }}</td>
                        <td class="py-3 text-gray-500 whitespace-nowrap">{{ ($post->published_at ?? $post->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="py-3">
                            <div class="flex items-center gap-3 whitespace-nowrap">
                                @if($post->isPubliclyVisible())
                                    <a href="https://{{ $tenant->slug }}.{{ config('app.base_domain') }}/blog/{{ $post->slug }}" target="_blank" class="text-gray-400 hover:text-gray-600" title="Lihat artikel di website">Lihat</a>
                                @endif
                                <a href="{{ route('tenant.posts.edit', $post->id) }}" class="text-primary hover:underline">Edit</a>
                                <form method="POST" action="{{ route('tenant.posts.toggle', $post->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-gray-500 hover:text-gray-700 hover:underline">{{ $post->status === 'published' ? 'Jadikan Draf' : 'Terbitkan' }}</button>
                                </form>
                                <form method="POST" action="{{ route('tenant.posts.destroy', $post->id) }}" onsubmit="return confirm('Pindahkan artikel \'{{ $post->title }}\' ke sampah?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-gray-500">Belum ada artikel. Klik <a href="{{ route('tenant.posts.create') }}" class="text-primary hover:underline">Tulis Artikel</a> untuk memulai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</div>
@endsection
