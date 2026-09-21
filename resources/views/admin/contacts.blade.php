@extends('admin.layout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Kontak</h1>

<div class="bg-white rounded-xl p-6 shadow-sm mb-6 max-w-2xl">
    <h2 class="text-lg font-semibold mb-4">Tambah Kontak</h2>
    <form method="POST" action="{{ route('tenant.contacts.store') }}">
        @csrf
        <div class="grid md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Label</label>
                <input type="text" name="label" placeholder="Telepon" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nilai</label>
                <input type="text" name="value" required placeholder="+62 812..." class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tipe</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="phone">Telepon</option>
                    <option value="email">Email</option>
                    <option value="address">Alamat</option>
                    <option value="social">Sosial Media</option>
                </select>
            </div>
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium">Tambah</button>
    </form>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Daftar Kontak</h2>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2">Label</th>
                <th class="py-2">Nilai</th>
                <th class="py-2">Tipe</th>
                <th class="py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
                <tr class="border-b">
                    <td class="py-3 font-medium">{{ $contact->label }}</td>
                    <td class="py-3">{{ $contact->value }}</td>
                    <td class="py-3 text-gray-500">{{ $contact->type }}</td>
                    <td class="py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('tenant.contacts.edit', $contact->id) }}" class="text-primary hover:underline">Edit</a>
                            <form method="POST" action="{{ route('tenant.contacts.destroy', $contact->id) }}" onsubmit="return confirm('Hapus kontak \'{{ $contact->label ?? $contact->value }}\'?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-4 text-center text-gray-500">Belum ada kontak.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
