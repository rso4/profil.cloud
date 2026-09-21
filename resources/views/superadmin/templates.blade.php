@extends('superadmin.layout')

@section('content')
<h1 class="text-2xl font-bold mb-6">Manajemen Template</h1>

{{-- Form Tambah Template --}}
<div class="bg-white rounded-xl p-6 shadow-sm mb-6 max-w-2xl">
    <h2 class="text-lg font-semibold mb-4">Tambah Template</h2>
    <form method="POST" action="{{ route('superadmin.templates.store') }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Slug (folder template)</label>
                <input type="text" name="slug" required placeholder="hotel-01" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nama Template</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="category" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="hotel">Hotel</option>
                    <option value="school">Sekolah</option>
                    <option value="sme">UMKM</option>
                    <option value="general">Umum</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">URL Thumbnail (opsional)</label>
                <input type="url" name="thumbnail" placeholder="https://..." class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2"></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">Tambah Template</button>
    </form>
</div>

{{-- Filter Tabs --}}
<div class="flex gap-2 mb-6 flex-wrap" id="filter-tabs">
    <button onclick="filterTemplates('all')" data-filter="all" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white">Semua ({{ $templates->count() }})</button>
    @foreach (['hotel' => 'Hotel', 'school' => 'Sekolah', 'sme' => 'UMKM', 'general' => 'Umum'] as $cat => $label)
        <button onclick="filterTemplates('{{ $cat }}')" data-filter="{{ $cat }}" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">
            {{ $label }} ({{ $templates->where('category', $cat)->count() }})
        </button>
    @endforeach
</div>

{{-- Template Grid --}}
<div id="template-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
    @foreach ($templates as $template)
        @php
            $prompt = config('template_images.prompts.'.$template->slug, $template->description ?? '');
            // Preview screenshot (dari Chromium) untuk halaman superadmin
            $thumbUrl = static_image($template->slug, 'preview-thumb', $prompt);
            $fullUrl = static_image($template->slug, 'preview-full', $prompt);
            $hasThumb = !empty($thumbUrl);
        @endphp
        <div class="template-card bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition group" data-category="{{ $template->category }}">
            {{-- Thumbnail (klik untuk membuka modal) --}}
            <div class="relative cursor-pointer overflow-hidden aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200" onclick="openModal('{{ $template->slug }}', '{{ $template->name }}', '{{ $fullUrl }}')">
                @if ($hasThumb)
                    {{-- Skeleton loading, hilang setelah gambar load --}}
                    <div class="absolute inset-0 skeleton-shimmer pointer-events-none"></div>
                    <img src="{{ $thumbUrl }}"
                         alt="{{ $template->name }}"
                         loading="lazy"
                         decoding="async"
                         class="thumbnail-img w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         onload="this.previousElementSibling.style.display='none'"
                         onerror="handleImageError(this, '{{ $template->name }}')">
                @else
                    {{-- Placeholder jika tidak ada thumbnail --}}
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                        <span class="text-5xl mb-2">🎨</span>
                        <span class="text-sm">Belum ada thumbnail</span>
                    </div>
                @endif

                {{-- Indikator zoom saat hover --}}
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center pointer-events-none">
                    <span class="opacity-0 group-hover:opacity-100 transition text-white text-sm font-medium bg-black/60 px-4 py-2 rounded-full flex items-center gap-1.5">
                        🔍 Klik untuk memperbesar
                    </span>
                </div>

                {{-- Category badge --}}
                <span class="absolute top-2 left-2 px-2.5 py-1 rounded-full text-xs font-medium bg-white/90 text-gray-700 capitalize shadow-sm">
                    {{ $template->category }}
                </span>

                {{-- Status badge --}}
                <span class="absolute top-2 right-2 px-2.5 py-1 rounded-full text-xs font-medium shadow-sm {{ $template->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $template->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            {{-- Info --}}
            <div class="p-4">
                <div class="flex items-start justify-between mb-1">
                    <h3 class="font-semibold text-gray-900">{{ $template->name }}</h3>
                    <code class="text-xs text-gray-400 font-mono">{{ $template->slug }}</code>
                </div>
                <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $template->description ?? 'Tidak ada deskripsi.' }}</p>
                <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                    @if ($hasThumb)
                        <button onclick="openModal('{{ $template->slug }}', '{{ $template->name }}', '{{ $fullUrl }}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                            🔍 Pratinjau
                        </button>
                    @endif
                    <form method="POST" action="{{ route('superadmin.templates.toggle', $template->id) }}" class="ml-auto">
                        @csrf
                        <button type="submit" class="text-sm font-medium {{ $template->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                            {{ $template->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Empty state --}}
<div id="empty-state" class="hidden text-center py-12 text-gray-400">
    <p class="text-lg">Tidak ada template pada kategori ini.</p>
</div>

{{-- Modal Preview dengan dukungan Fullscreen API --}}
<div id="previewModal" class="hidden fixed inset-0 z-[100] items-center justify-center p-4 bg-black/80 backdrop-blur-sm" onclick="closeModal(event)">
    <div id="modalContent" class="relative max-w-5xl w-full max-h-[90vh] bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
            <div>
                <h3 id="modalTitle" class="text-lg font-bold text-gray-900">Nama Template</h3>
                <code id="modalSlug" class="text-xs text-gray-400 font-mono">slug</code>
            </div>
            <div class="flex items-center gap-2">
                {{-- Tombol Fullscreen --}}
                <button id="fullscreenBtn"
                        onclick="toggleFullscreen()"
                        class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-700 text-xl transition"
                        title="Layar penuh"
                        aria-label="Toggle fullscreen">
                    ⛶
                </button>
                {{-- Tombol Tutup --}}
                <button onclick="closeModal()" class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 text-xl transition" aria-label="Close">✕</button>
            </div>
        </div>

        {{-- Image Container (untuk Fullscreen API) --}}
        <div id="imageContainer" class="bg-gray-100 flex items-center justify-center overflow-auto flex-1 min-h-0">
            {{-- Skeleton --}}
            <div id="modalSkeleton" class="absolute inset-0 skeleton-shimmer"></div>
            <img id="modalImage" alt="Preview"
                 class="w-full h-auto relative z-10"
                 onload="document.getElementById('modalSkeleton').style.display='none'"
                 onerror="handleModalImageError()">
        </div>

        {{-- Footer info --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-500 flex items-center justify-between flex-shrink-0">
            <span>Tekan ESC untuk keluar • Klik di luar untuk menutup</span>
            <span id="fullscreenStatus" class="text-gray-400"></span>
        </div>
    </div>
</div>

{{-- CSS: Skeleton shimmer animation --}}
<style>
.skeleton-shimmer {
    background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.6) 50%, rgba(255,255,255,0) 100%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    background-color: #f3f4f6;
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Fullscreen state */
:fullscreen .fullscreen-hide,
:-webkit-full-screen .fullscreen-hide { display: none !important; }
:fullscreen #modalContent,
:-webkit-full-screen #modalContent {
    max-width: 100vw !important;
    max-height: 100vh !important;
    border-radius: 0 !important;
}
:fullscreen #imageContainer,
:-webkit-full-screen #imageContainer {
    max-height: calc(100vh - 72px) !important;
}
</style>

{{-- JS: Modal + Filter + Fullscreen API --}}
<script>
(function() {
    'use strict';

    // State
    let modalState = {
        isOpen: false,
        isFullscreen: false,
    };

    // Cross-browser Fullscreen API helper
    function getFullscreenElement() {
        return document.fullscreenElement
            || document.webkitFullscreenElement
            || document.mozFullScreenElement
            || document.msFullscreenElement
            || null;
    }

    function requestFullscreen(el) {
        const fn = el.requestFullscreen
            || el.webkitRequestFullscreen
            || el.mozRequestFullScreen
            || el.msRequestFullscreen;
        if (fn) {
            return fn.call(el);
        }
        return Promise.reject(new Error('Fullscreen API not supported'));
    }

    function exitFullscreen() {
        const fn = document.exitFullscreen
            || document.webkitExitFullscreen
            || document.mozCancelFullScreen
            || document.msExitFullscreen;
        if (fn) {
            return fn.call(document);
        }
        return Promise.reject(new Error('Exit fullscreen not supported'));
    }

    function isFullscreenSupported() {
        const el = document.documentElement;
        return !!(el.requestFullscreen
            || el.webkitRequestFullscreen
            || el.mozRequestFullScreen
            || el.msRequestFullscreen);
    }

    // Window expose
    window.toggleFullscreen = function() {
        const content = document.getElementById('modalContent');
        if (!content) return;

        if (getFullscreenElement()) {
            exitFullscreen().catch(e => console.warn('Exit fullscreen failed:', e));
        } else {
            requestFullscreen(content).catch(e => {
                console.warn('Fullscreen request failed:', e);
                alert('Browser tidak mendukung mode layar penuh. Coba tekan F11.');
            });
        }
    };

    // Listen to fullscreen change events (semua browser)
    ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'msfullscreenchange'].forEach(ev => {
        document.addEventListener(ev, () => {
            modalState.isFullscreen = !!getFullscreenElement();
            const btn = document.getElementById('fullscreenBtn');
            const status = document.getElementById('fullscreenStatus');
            if (btn) {
                btn.textContent = modalState.isFullscreen ? '⛶' : '⛶';
                btn.title = modalState.isFullscreen ? 'Keluar layar penuh' : 'Layar penuh';
            }
            if (status) {
                status.textContent = modalState.isFullscreen ? '🟢 Fullscreen aktif' : '';
            }
        });
    });

    // Modal open
    window.openModal = function(slug, name, thumbnail) {
        const modal = document.getElementById('previewModal');
        document.getElementById('modalTitle').textContent = name;
        document.getElementById('modalSlug').textContent = slug;
        const img = document.getElementById('modalImage');
        const skeleton = document.getElementById('modalSkeleton');

        // Bersihkan placeholder error dari pembukaan modal sebelumnya
        const prevError = document.getElementById('modalImageError');
        if (prevError) prevError.remove();

        if (thumbnail) {
            skeleton.style.display = '';
            img.style.display = 'block';
            img.src = thumbnail;
        } else {
            skeleton.style.display = 'none';
            img.style.display = 'none';
        }

        // Sembunyikan tombol fullscreen jika tidak didukung
        if (!isFullscreenSupported()) {
            const btn = document.getElementById('fullscreenBtn');
            if (btn) btn.style.display = 'none';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        modalState.isOpen = true;
    };

    window.closeModal = function(e) {
        if (e && e.target !== e.currentTarget) return;
        const modal = document.getElementById('previewModal');

        // Exit fullscreen jika aktif
        if (getFullscreenElement()) {
            exitFullscreen().catch(() => {});
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        modalState.isOpen = false;
    };

    // ESC handler
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            // Jika fullscreen aktif, biarkan browser keluar dulu
            if (getFullscreenElement()) return;
            if (modalState.isOpen) window.closeModal();
        }
    });

    // Image error handler untuk modal
    // (didefinisikan global di superadmin/layout agar tersedia sebelum elemen <img> dimuat)
    if (typeof window.handleModalImageError !== 'function') {
        window.handleModalImageError = function() {
            const skeleton = document.getElementById('modalSkeleton');
            if (skeleton) skeleton.style.display = 'none';
            const container = document.getElementById('imageContainer');
            container.innerHTML = '<div class="text-center text-gray-500 py-12"><p class="text-lg mb-2">⚠️ Gambar tidak dapat dimuat</p><p class="text-sm">Silakan coba lagi atau hubungi administrator.</p></div>';
        };
    }

    // Image error handler untuk thumbnail card
    window.handleImageError = function(img, name) {
        const parent = img.parentElement;
        const skeleton = parent.querySelector('.skeleton-shimmer');
        if (skeleton) skeleton.style.display = 'none';
        img.style.display = 'none';
        const placeholder = document.createElement('div');
        placeholder.className = 'w-full h-full flex flex-col items-center justify-center text-gray-400';
        placeholder.innerHTML = '<span class="text-5xl mb-2">🎨</span><span class="text-sm">' + name + '</span>';
        parent.appendChild(placeholder);
    };

    // Filter
    window.filterTemplates = function(category) {
        const cards = document.querySelectorAll('.template-card');
        let visible = 0;
        cards.forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = '';
                visible++;
            } else {
                card.style.display = 'none';
            }
        });
        document.getElementById('empty-state').classList.toggle('hidden', visible > 0);
        document.querySelectorAll('.filter-btn').forEach(btn => {
            if (btn.dataset.filter === category) {
                btn.classList.add('bg-blue-600', 'text-white');
                btn.classList.remove('bg-white', 'border', 'border-gray-300', 'text-gray-700');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-white', 'border', 'border-gray-300', 'text-gray-700');
            }
        });
    };
})();
</script>
@endsection