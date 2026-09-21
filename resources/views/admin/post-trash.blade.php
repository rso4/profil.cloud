@extends('admin.layout')

@section('title', 'Sampah Artikel')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold">Sampah Artikel</h1>
        <p class="text-sm text-gray-500">Artikel terhapus dapat dipulihkan atau dihapus permanen.</p>
    </div>
    <a href="{{ route('tenant.posts') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali ke Artikel</a>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">Judul</th>
                    <th class="py-2">URL</th>
                    <th class="py-2">Kategori</th>
                    <th class="py-2">Dihapus</th>
                    <th class="py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr class="border-b">
                        <td class="py-3 font-medium">{{ $post->title }}</td>
                        <td class="py-3 text-gray-500">/blog/{{ $post->slug }}</td>
                        <td class="py-3">{{ $post->category?->name ?? '—' }}</td>
                        <td class="py-3 text-gray-500 whitespace-nowrap">{{ $post->deleted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="py-3">
                            <div class="flex items-center gap-3 whitespace-nowrap">
                                <form method="POST" action="{{ route('tenant.posts.restore', $post->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:underline">Pulihkan</button>
                                </form>
                                <form method="POST" action="{{ route('tenant.posts.force', $post->id) }}"
                                    onsubmit="return confirm('Hapus permanen artikel \'{{ $post->title }}\'? Tindakan ini TIDAK dapat dibatalkan.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus Permanen</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-gray-500">Sampah kosong. Artikel yang dihapus akan muncul di sini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</div>
@endsection
