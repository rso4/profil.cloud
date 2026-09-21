<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Cloud - Platform Website Profile Multi-Tenant</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <span class="text-2xl font-bold text-blue-600">Profil Cloud</span>
            <a href="{{ route('login') }}" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-medium">Login</a>
        </div>
    </nav>

    <section class="bg-gradient-to-br from-blue-600 to-indigo-800 text-white">
        <div class="max-w-7xl mx-auto px-4 py-24 text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold mb-6">Platform Website Profile Multi-Tenant</h1>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">Buat website profile profesional untuk bisnis Anda dengan subdomain otomatis, tema warna kustom, dan manajemen konten yang mudah.</p>
            <a href="{{ route('login') }}" class="bg-white text-blue-700 px-8 py-3 rounded-lg font-semibold">Mulai Sekarang</a>
        </div>
    </section>

    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl p-8 shadow-sm">
                <div class="text-4xl mb-4">🌐</div>
                <h3 class="text-xl font-semibold mb-2">Subdomain Otomatis</h3>
                <p class="text-gray-600">Setiap tenant mendapatkan subdomain unik yang terhubung otomatis ke website mereka.</p>
            </div>
            <div class="bg-white rounded-xl p-8 shadow-sm">
                <div class="text-4xl mb-4">🎨</div>
                <h3 class="text-xl font-semibold mb-2">Tema Warna Kustom</h3>
                <p class="text-gray-600">Pilih warna tema sesuai brand Anda, diterapkan secara instan tanpa mengubah kode.</p>
            </div>
            <div class="bg-white rounded-xl p-8 shadow-sm">
                <div class="text-4xl mb-4">🛡️</div>
                <h3 class="text-xl font-semibold mb-2">Isolasi Data Aman</h3>
                <p class="text-gray-600">Data setiap tenant terisolasi penuh dengan RBAC dan scoping tenant_id.</p>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-gray-400 py-6 text-center">
        <p>&copy; {{ date('Y') }} Profil Cloud. All rights reserved.</p>
    </footer>
</body>
</html>
