<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel Admin' }} - {{ $tenant->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary-color: {{ $tenant->primary_color ?? '#2563eb' }};
            --secondary-color: {{ $tenant->secondary_color ?? '#0f172a' }};
        }
        .bg-primary { background-color: var(--primary-color); }
        .text-primary { color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-secondary text-white flex-shrink-0">
            <div class="p-6">
                <h1 class="text-xl font-bold">{{ $tenant->name }}</h1>
                <p class="text-sm text-gray-400">Panel Admin Tenant</p>
            </div>
            <nav class="mt-4">
                <a href="{{ route('tenant.dashboard') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.dashboard') ? 'bg-white/10' : '' }}">📊 Dashboard</a>
                <a href="{{ route('tenant.profile.edit') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.profile.*') ? 'bg-white/10' : '' }}">🏢 Profil Bisnis</a>
                <a href="{{ route('tenant.theme.edit') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.theme.*') ? 'bg-white/10' : '' }}">🎨 Tema & Warna</a>
                <a href="{{ route('tenant.services') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.services*') ? 'bg-white/10' : '' }}">🛎️ Layanan</a>
                <a href="{{ route('tenant.galleries') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.galleries*') ? 'bg-white/10' : '' }}">🖼️ Galeri</a>
                <a href="{{ route('tenant.contacts') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.contacts*') ? 'bg-white/10' : '' }}">📞 Kontak</a>
                <a href="{{ route('tenant.pages') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.pages*') ? 'bg-white/10' : '' }}">📄 Halaman Custom</a>
                <a href="{{ route('tenant.posts') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.posts*') ? 'bg-white/10' : '' }}">📝 Artikel</a>
                <a href="{{ route('tenant.categories') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.categories*') ? 'bg-white/10' : '' }}">🗂️ Kategori</a>
                <a href="{{ route('tenant.media') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('tenant.media*') ? 'bg-white/10' : '' }}">📎 Media</a>
            </nav>
            <div class="p-6 mt-auto">
                <a href="https://{{ $tenant->slug }}.{{ config('app.base_domain') }}" target="_blank" class="block text-sm text-blue-300 hover:underline mb-4">🌐 Lihat Website</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg text-sm">Logout</button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
                    <ul class="list-disc pl-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    {{-- Stack modals: render di luar semua form agar tidak terjadi nested form --}}
    @stack('modals')
    @stack('scripts')
</body>
</html>
