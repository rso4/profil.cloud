{{-- Komponen rich text editor (vanilla JS, tanpa build step).
     - Toolbar: format teks, heading, list, quote, link, gambar (via media picker), HTML source.
     - Sinkron otomatis ke <textarea name=...> saat input & submit form.
     - Modal media picker (upload/list) dirender sekali per halaman via @once. --}}

@props(['name', 'value' => '', 'placeholder' => 'Tulis konten di sini...', 'target' => null])

{{-- $target opsional: identifier unik editor (data-target) jika ada >1 editor
     dengan nama field yang sama dalam satu halaman, mis. form tambah + modal edit. --}}
<div data-rich-editor data-target="{{ $target ?? $name }}">
    <div class="rich-editor-toolbar flex flex-wrap items-center gap-1 border border-gray-300 border-b-0 rounded-t-lg bg-gray-50 p-2">
        <button type="button" data-cmd="bold" class="px-2.5 py-1 text-xs rounded hover:bg-gray-200 font-bold" title="Tebal (Ctrl+B)">B</button>
        <button type="button" data-cmd="italic" class="px-2.5 py-1 text-xs rounded hover:bg-gray-200 italic" title="Miring (Ctrl+I)">I</button>
        <button type="button" data-cmd="underline" class="px-2.5 py-1 text-xs rounded hover:bg-gray-200 underline" title="Garis bawah">U</button>
        <button type="button" data-cmd="strikeThrough" class="px-2.5 py-1 text-xs rounded hover:bg-gray-200 line-through" title="Coret">S</button>
        <span class="w-px h-4 bg-gray-300 mx-1"></span>
        <button type="button" data-cmd="formatBlock" data-arg="h2" class="px-2 py-1 text-xs rounded hover:bg-gray-200 font-semibold" title="Judul H2">H2</button>
        <button type="button" data-cmd="formatBlock" data-arg="h3" class="px-2 py-1 text-xs rounded hover:bg-gray-200 font-semibold" title="Sub judul H3">H3</button>
        <button type="button" data-cmd="formatBlock" data-arg="blockquote" class="px-2.5 py-1 text-xs rounded hover:bg-gray-200" title="Kutipan">&#10077;</button>
        <button type="button" data-cmd="insertUnorderedList" class="px-2.5 py-1 text-xs rounded hover:bg-gray-200" title="Daftar poin">&#8226;</button>
        <button type="button" data-cmd="insertOrderedList" class="px-2.5 py-1 text-xs rounded hover:bg-gray-200" title="Daftar bernomor">1.</button>
        <span class="w-px h-4 bg-gray-300 mx-1"></span>
        <button type="button" data-cmd="link" class="px-2 py-1 text-xs rounded hover:bg-gray-200" title="Sisipkan link">&#128279;</button>
        <button type="button" data-cmd="unlink" class="px-2 py-1 text-xs rounded hover:bg-gray-200" title="Hapus link">&#128683;</button>
        <button type="button" data-cmd="image" class="px-2 py-1 text-xs rounded hover:bg-gray-200" title="Sisipkan gambar dari media library">&#128247;</button>
        <button type="button" data-cmd="insertHorizontalRule" class="px-2 py-1 text-xs rounded hover:bg-gray-200" title="Garis pemisah">&mdash;</button>
        <span class="w-px h-4 bg-gray-300 mx-1"></span>
        <button type="button" data-cmd="removeFormat" class="px-2 py-1 text-xs rounded hover:bg-gray-200" title="Bersihkan format">&#10683;</button>
        <button type="button" data-cmd="source" class="px-2 py-1 text-xs rounded hover:bg-gray-200 font-mono" title="Lihat / edit kode HTML">&lt;/&gt;</button>
    </div>
    <div class="rich-editor-area border border-gray-300 rounded-b-lg px-4 py-3 min-h-[220px] bg-white focus:outline-none focus:ring-2 focus:ring-primary/30" contenteditable="true" data-placeholder="{{ $placeholder }}">{!! $value !!}</div>
    <textarea name="{{ $name }}" class="rich-editor-source hidden w-full border border-gray-300 rounded-b-lg px-4 py-3 min-h-[220px] font-mono text-sm bg-gray-50" rows="10" placeholder="Kode HTML...">{{ $value }}</textarea>
</div>

<style>
    .rich-editor-area:empty::before { content: attr(data-placeholder); color: #9ca3af; pointer-events: none; }
    .rich-editor-area h2 { font-size: 1.35rem; font-weight: 700; margin: 1.1rem 0 .5rem; }
    .rich-editor-area h3 { font-size: 1.15rem; font-weight: 700; margin: 1rem 0 .5rem; }
    .rich-editor-area p { margin: .5rem 0; }
    .rich-editor-area ul { list-style: disc; padding-left: 1.5rem; margin: .5rem 0; }
    .rich-editor-area ol { list-style: decimal; padding-left: 1.5rem; margin: .5rem 0; }
    .rich-editor-area blockquote { border-left: 4px solid #d1d5db; padding-left: 1rem; margin: .75rem 0; color: #6b7280; font-style: italic; }
    .rich-editor-area img { max-width: 100%; height: auto; border-radius: .5rem; margin: .5rem 0; }
    .rich-editor-area hr { margin: 1.25rem 0; border-color: #e5e7eb; }
</style>

@once
    {{-- Modal Media Picker: dipakai editor & pemilih cover artikel.
         Dipush ke stack "modals" agar dirender DI LUAR form induk (nested form
         membuat parser HTML membuang tag form picker, sehingga input file required
         tersembunyi ikut tervalidasi dan memblokir submit form induk). --}}
    @push('modals')
        <div id="media-picker-modal" class="fixed inset-0 z-[60] hidden bg-black/50 items-center justify-center p-4">
            <div class="bg-white rounded-xl w-full max-w-3xl max-h-[85vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-4 border-b">
                    <h2 class="text-lg font-semibold">Media Library</h2>
                    <button type="button" id="media-picker-close" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <div class="p-4 border-b bg-gray-50">
                    <form id="media-picker-upload" class="flex flex-wrap items-center gap-2">
                        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="text-sm flex-1 min-w-[180px]" required>
                        <input type="text" name="alt_text" placeholder="Alt text (opsional)" class="border border-gray-300 rounded-lg px-3 py-2 text-sm flex-1 min-w-[140px]">
                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm whitespace-nowrap">Upload &amp; Pakai</button>
                    </form>
                </div>
                <div class="p-4">
                    <input type="text" id="media-picker-search" placeholder="Cari nama file..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3">
                    <div id="media-picker-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-3 overflow-y-auto max-h-[45vh]"></div>
                    <p id="media-picker-empty" class="hidden text-center text-gray-500 py-8 text-sm">Belum ada media. Upload gambar di atas.</p>
                </div>
            </div>
        </div>
    @endpush
    @push('scripts')
        <script src="{{ asset('js/rich-editor.js') }}"></script>
    @endpush
@endonce
