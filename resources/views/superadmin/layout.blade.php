<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Super Admin' }} - Profil Cloud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Handler global untuk error gambar modal — dideklarasikan sebelum
        // elemen <img> dimuat agar onerror tidak memicu ReferenceError.
        // Body hanya dieksekusi saat dipanggil (setelah DOM siap).
        window.handleModalImageError = function () {
            var skeleton = document.getElementById('modalSkeleton');
            if (skeleton) skeleton.style.display = 'none';
            var img = document.getElementById('modalImage');
            if (img) img.style.display = 'none';
            var container = document.getElementById('imageContainer');
            if (container && !document.getElementById('modalImageError')) {
                var placeholder = document.createElement('div');
                placeholder.id = 'modalImageError';
                placeholder.className = 'text-center text-gray-500 py-12';
                placeholder.innerHTML = '<p class="text-lg mb-2">⚠️ Gambar tidak dapat dimuat</p><p class="text-sm">Silakan coba lagi atau hubungi administrator.</p>';
                container.appendChild(placeholder);
            }
        };
    </script>
</head>
<body class="bg-gray-100 text-gray-800 antialiased">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-900 text-white flex-shrink-0">
            <div class="p-6">
                <h1 class="text-xl font-bold">Profil Cloud</h1>
                <p class="text-sm text-gray-400">Super Admin Panel</p>
            </div>
            <nav class="mt-4">
                <a href="{{ route('superadmin.dashboard') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('superadmin.dashboard') ? 'bg-white/10' : '' }}">📊 Dashboard</a>
                <a href="{{ route('superadmin.tenants') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('superadmin.tenants*') ? 'bg-white/10' : '' }}">🏢 Tenant</a>
                <a href="{{ route('superadmin.templates') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('superadmin.templates*') ? 'bg-white/10' : '' }}">🎨 Template</a>
                <a href="{{ route('superadmin.users') }}" class="block px-6 py-3 hover:bg-white/10 {{ request()->routeIs('superadmin.users*') ? 'bg-white/10' : '' }}">👥 User</a>
            </nav>
            <div class="p-6 mt-auto">
                <a href="{{ url('/') }}" class="block text-sm text-blue-300 hover:underline mb-4">🌐 Lihat Website</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg text-sm">Logout</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6">{{ session('success') }}</div>
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
</body>
</html>
