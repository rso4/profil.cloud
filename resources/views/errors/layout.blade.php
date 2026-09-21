<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Error' }} - Profil Cloud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased flex items-center justify-center min-h-screen">
    <div class="text-center px-6">
        <div class="text-7xl font-extrabold text-blue-600 mb-4">{{ $code ?? 'Error' }}</div>
        <h1 class="text-2xl font-bold mb-2">{{ $title ?? 'Terjadi Kesalahan' }}</h1>
        <p class="text-gray-500 mb-8">{{ $message ?? 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.' }}</p>
        <a href="{{ url('/') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700">Kembali ke Beranda</a>
    </div>
</body>
</html>
