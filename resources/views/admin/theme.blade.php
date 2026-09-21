@extends('admin.layout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Tema & Warna</h1>

<div class="bg-white rounded-xl p-6 shadow-sm max-w-2xl">
    <p class="text-gray-500 mb-6">Pilih warna tema website Anda. Perubahan diterapkan secara instan.</p>

    <form method="POST" action="{{ route('tenant.theme.update') }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium mb-2">Warna Primer</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="primary_color" value="{{ $tenant->primary_color }}" class="w-16 h-16 rounded-lg border border-gray-300 cursor-pointer">
                    <input type="text" name="primary_color_hex" value="{{ $tenant->primary_color }}" class="border border-gray-300 rounded-lg px-4 py-2 w-32" readonly>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Warna Sekunder</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="secondary_color" value="{{ $tenant->secondary_color }}" class="w-16 h-16 rounded-lg border border-gray-300 cursor-pointer">
                    <input type="text" name="secondary_color_hex" value="{{ $tenant->secondary_color }}" class="border border-gray-300 rounded-lg px-4 py-2 w-32" readonly>
                </div>
            </div>
        </div>

        {{-- Preview --}}
        <div class="mb-6 p-6 rounded-xl" style="background: {{ $tenant->secondary_color }}">
            <div class="text-white font-bold mb-2">Pratinjau Tema</div>
            <button type="button" class="text-white px-4 py-2 rounded-lg" style="background: {{ $tenant->primary_color }}">Tombol Contoh</button>
        </div>

        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium">Simpan Tema</button>
    </form>
</div>

<script>
    // Sinkronkan color picker dengan input HEX
    document.querySelectorAll('input[type=color]').forEach(function(picker) {
        picker.addEventListener('input', function() {
            var hexInput = this.closest('div').querySelector('input[type=text]');
            if (hexInput) hexInput.value = this.value;
        });
    });
</script>
@endsection
