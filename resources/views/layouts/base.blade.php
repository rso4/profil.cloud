<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name ?? 'Profil Cloud' }} - {{ $pageTitle ?? '' }}</title>
    <meta name="description" content="{{ $metaDescription ?? $tenant->profile->description ?? '' }}">

    {{-- Tema dinamis: variabel CSS disuntikkan dari konfigurasi warna tenant --}}
    <style>
        :root {
            --primary-color: {{ $tenant->primary_color ?? '#2563eb' }};
            --secondary-color: {{ $tenant->secondary_color ?? '#0f172a' }};
            --primary-rgb: {{ hex_to_rgb($tenant->primary_color ?? '#2563eb') }};
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .btn-primary { background-color: var(--primary-color); }
        .btn-primary:hover { filter: brightness(1.1); }
        .text-primary { color: var(--primary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
        .bg-secondary { background-color: var(--secondary-color); }
        .text-secondary { color: var(--secondary-color); }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    @yield('content')

    {{-- Modal Fullscreen Galeri --}}
    <div id="gallery-modal" class="fixed inset-0 z-[100] hidden bg-black/90 items-center justify-center">
        <button type="button" id="gallery-modal-close" class="absolute top-4 right-4 text-white text-3xl leading-none w-10 h-10 hover:bg-white/10 rounded-full z-10">&times;</button>
        <button type="button" id="gallery-modal-toggle" class="absolute bottom-4 right-4 bg-white/10 text-white px-4 py-2 rounded-lg text-sm hover:bg-white/20 z-10">⛶ Layar Penuh</button>
        <img id="gallery-modal-image" src="" alt="" class="max-w-full max-h-full object-contain">
        <p id="gallery-modal-title" class="absolute bottom-4 left-4 text-white text-sm"></p>
    </div>

    <script>
    (function () {
        'use strict';

        var modal = document.getElementById('gallery-modal');
        var modalImg = document.getElementById('gallery-modal-image');
        var modalTitle = document.getElementById('gallery-modal-title');
        var closeBtn = document.getElementById('gallery-modal-close');
        var toggleBtn = document.getElementById('gallery-modal-toggle');

        function openModal(img) {
            // Prioritas: URL resolusi penuh (data-fullscreen) > src thumbnail
            modalImg.src = img.getAttribute('data-fullscreen') || img.getAttribute('src');
            modalImg.alt = img.getAttribute('alt') || '';
            modalTitle.textContent = img.getAttribute('alt') || '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            if (document.fullscreenElement) {
                document.exitFullscreen().catch(function () {});
            }
        }

        // Delegasi klik: semua gambar galeri bertanda data-gallery
        document.addEventListener('click', function (e) {
            var img = e.target.closest('img[data-gallery]');
            if (img) {
                e.preventDefault();
                openModal(img);
            }
        });

        closeBtn.addEventListener('click', closeModal);

        // Toggle Fullscreen API (cross-browser)
        toggleBtn.addEventListener('click', function () {
            if (document.fullscreenElement) {
                document.exitFullscreen().catch(function () {});
            } else {
                var el = modal;
                var fn = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
                if (fn) {
                    fn.call(el);
                }
            }
        });

        // Tutup dengan ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        // Tutup saat klik di luar gambar
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    })();
    </script>
</body>
</html>
