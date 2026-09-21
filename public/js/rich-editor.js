/* global document, window, fetch, FormData, FileReader */
(function () {
    'use strict';

    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    // ================= Rich Text Editor =================

    function initEditor(wrapper) {
        var area = wrapper.querySelector('.rich-editor-area');
        var source = wrapper.querySelector('.rich-editor-source');
        var toolbar = wrapper.querySelector('.rich-editor-toolbar');

        if (!area || !source || area.dataset.ready === '1') return;
        area.dataset.ready = '1';

        // Sinkronisasi konten editor -> textarea (sumber data form)
        var sync = function () { source.value = area.innerHTML; };
        area.addEventListener('input', sync);
        area.addEventListener('blur', sync);

        // Paragraf default menggunakan <p> agar konsisten dengan sanitizer
        try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) { /* noop */ }

        // Toolbar: jangan curi seleksi saat klik tombol
        toolbar.addEventListener('mousedown', function (e) {
            if (e.target.closest('button')) e.preventDefault();
        });

        toolbar.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-cmd]');
            if (!btn || !wrapper.contains(btn)) return;

            var cmd = btn.dataset.cmd;
            var arg = btn.dataset.arg || null;
            area.focus();

            if (cmd === 'source') {
                toggleSource(wrapper, area, source);
                return;
            }

            if (cmd === 'image') {
                var range = saveSelection(area);
                window.MediaPicker.open(function (media) {
                    restoreSelection(area, range);
                    document.execCommand('insertHTML', false,
                        '<img src="' + media.url + '" alt="' + escapeHtml(media.alt || media.name || '') + '">');
                    sync();
                });
                return;
            }

            if (cmd === 'link') {
                var url = window.prompt('Masukkan URL link (https://... atau /p/halaman):', 'https://');
                if (url && url !== 'https://') {
                    document.execCommand('createLink', false, url.trim());
                }
                return;
            }

            if (cmd === 'unlink') {
                document.execCommand('unlink', false, null);
                return;
            }

            if (cmd === 'formatBlock') {
                document.execCommand('formatBlock', false, '<' + arg + '>');
                return;
            }

            document.execCommand(cmd, false, null);
            sync();
        });

        // Sinkron saat form disubmit (termasuk submit via JS)
        var form = area.closest('form');
        if (form) form.addEventListener('submit', sync);
    }

    function toggleSource(wrapper, area, source) {
        var showingSource = source.classList.contains('hidden');
        if (showingSource) {
            source.value = area.innerHTML;
            source.classList.remove('hidden');
            area.classList.add('hidden');
        } else {
            area.innerHTML = source.value;
            source.classList.add('hidden');
            area.classList.remove('hidden');
        }
        wrapper.querySelectorAll('[data-cmd]').forEach(function (btn) {
            btn.classList.toggle('opacity-40', !showingSource ? btn.dataset.cmd === 'source' : false);
        });
    }

    function saveSelection(area) {
        var sel = window.getSelection();
        if (sel.rangeCount > 0 && area.contains(sel.anchorNode)) {
            return sel.getRangeAt(0).cloneRange();
        }
        return null;
    }

    function restoreSelection(area, range) {
        area.focus();
        if (range) {
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML.replace(/"/g, '&quot;');
    }

    window.RichEditor = {
        initAll: function () {
            document.querySelectorAll('[data-rich-editor]').forEach(initEditor);
        },
        // Set konten editor dari luar (mis. modal edit)
        set: function (name, html) {
            var wrapper = document.querySelector('[data-rich-editor][data-target="' + name + '"]');
            if (!wrapper) return;
            var area = wrapper.querySelector('.rich-editor-area');
            var source = wrapper.querySelector('.rich-editor-source');
            area.innerHTML = html || '';
            if (source) source.value = html || '';
        }
    };

    // ================= Media Picker Modal =================

    var pickerState = { callback: null };

    var pickerModal = document.getElementById('media-picker-modal');

    function pickerGrid() { return document.getElementById('media-picker-grid'); }
    function pickerEmpty() { return document.getElementById('media-picker-empty'); }

    window.MediaPicker = {
        open: function (callback) {
            pickerState.callback = callback;
            loadPicker();
            showModal(pickerModal);
        },
        close: function () {
            pickerState.callback = null;
            hideModal(pickerModal);
        }
    };

    function showModal(modal) {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function hideModal(modal) {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function loadPicker(query) {
        var grid = pickerGrid();
        if (!grid) return;
        grid.innerHTML = '<p class="col-span-full text-center text-gray-400 py-8 text-sm">Memuat media...</p>';

        var url = '/admin/media/list';
        if (query) url += '?q=' + encodeURIComponent(query);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                renderPicker(data.data || []);
            })
            .catch(function () {
                grid.innerHTML = '<p class="col-span-full text-center text-red-500 py-8 text-sm">Gagal memuat media.</p>';
            });
    }

    function renderPicker(items) {
        var grid = pickerGrid();
        var empty = pickerEmpty();
        grid.innerHTML = '';

        if (!items.length) {
            if (empty) empty.classList.remove('hidden');
            return;
        }
        if (empty) empty.classList.add('hidden');

        items.forEach(function (item) {
            var cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'relative group rounded-lg overflow-hidden border border-gray-200 hover:border-primary transition';
            cell.innerHTML =
                '<img src="' + item.thumb + '" alt="' + escapeHtml(item.alt || item.name) + '" class="w-full h-28 object-cover">' +
                '<span class="block truncate text-xs text-gray-500 px-2 py-1 bg-white">' + escapeHtml(item.name) + '</span>';
            cell.addEventListener('click', function () {
                if (typeof pickerState.callback === 'function') {
                    pickerState.callback(item);
                }
                window.MediaPicker.close();
            });
            grid.appendChild(cell);
        });
    }

    // Upload dari dalam picker
    var pickerUploadForm = document.getElementById('media-picker-upload');
    if (pickerUploadForm) {
        pickerUploadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fileInput = pickerUploadForm.querySelector('input[type="file"]');
            var altInput = pickerUploadForm.querySelector('input[name="alt_text"]');
            var file = fileInput.files[0];
            if (!file) return;

            var data = new FormData();
            data.append('file', file);
            if (altInput && altInput.value) data.append('alt_text', altInput.value);

            fetch('/admin/media', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: data
            })
                .then(function (res) { return res.json(); })
                .then(function (media) {
                    fileInput.value = '';
                    if (altInput) altInput.value = '';
                    loadPicker();
                    if (typeof pickerState.callback === 'function') {
                        pickerState.callback(media);
                        window.MediaPicker.close();
                    }
                })
                .catch(function () {
                    window.alert('Upload gagal. Pastikan file gambar (max 5MB).');
                });
        });
    }

    // Pencarian picker
    var pickerSearch = document.getElementById('media-picker-search');
    if (pickerSearch) {
        var debounce;
        pickerSearch.addEventListener('input', function () {
            window.clearTimeout(debounce);
            debounce = window.setTimeout(function () {
                loadPicker(pickerSearch.value.trim());
            }, 300);
        });
    }

    var pickerClose = document.getElementById('media-picker-close');
    if (pickerClose) pickerClose.addEventListener('click', window.MediaPicker.close);

    if (pickerModal) {
        pickerModal.addEventListener('click', function (e) {
            if (e.target === pickerModal) window.MediaPicker.close();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && pickerModal && !pickerModal.classList.contains('hidden')) {
            window.MediaPicker.close();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.RichEditor.initAll);
    } else {
        window.RichEditor.initAll();
    }
})();
