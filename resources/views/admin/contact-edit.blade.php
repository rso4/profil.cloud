@extends('admin.layout')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Edit Kontak</h1>
    <a href="{{ route('tenant.contacts') }}" class="text-sm text-primary hover:underline">&larr; Kembali ke Daftar Kontak</a>
</div>

<div class="bg-white rounded-xl p-6 shadow-sm max-w-2xl">
    <form method="POST" action="{{ route('tenant.contacts.update', $contact->id) }}">
        @csrf
        <div class="grid md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Label</label>
                <input type="text" name="label" value="{{ old('label', $contact->label) }}" placeholder="Telepon" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nilai <span class="text-red-500">*</span></label>
                <input type="text" name="value" required value="{{ old('value', $contact->value) }}" placeholder="+62 812..." class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tipe</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    @foreach(['phone' => 'Telepon', 'email' => 'Email', 'address' => 'Alamat', 'social' => 'Sosial Media'] as $key => $label)
                        <option value="{{ $key }}" @selected(old('type', $contact->type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium">Simpan Perubahan</button>
            <a href="{{ route('tenant.contacts') }}" class="border border-gray-300 px-6 py-2 rounded-lg">Batal</a>
        </div>
    </form>
</div>
@endsection