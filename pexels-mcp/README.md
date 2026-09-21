# Pexels MCP Server

MCP (Model Context Protocol) server untuk mencari **gambar** dan **video pendek** dari [Pexels](https://www.pexels.com) API. Server ini bersifat umum dan dapat di-install di sistem manapun yang memiliki Node.js ≥ 18, termasuk **Trae IDE**.

## Fitur

| Tool | Deskripsi |
|------|-----------|
| `search_photos` | Mencari foto berdasarkan kata kunci, dengan filter orientasi, ukuran, warna, dan bahasa |
| `get_photo` | Mengambil detail satu foto berdasarkan ID |
| `search_videos` | Mencari video pendek berdasarkan kata kunci, mengembalikan URL video berbagai kualitas + thumbnail |
| `get_video` | Mengambil detail satu video berdasarkan ID |
| `get_curated_photos` | Daftar foto pilihan (curated) tanpa kata kunci |
| `get_popular_videos` | Daftar video populer tanpa kata kunci |

## Persyaratan

- **Node.js ≥ 18** (disarankan 20 LTS atau 22)
- **API Key Pexels** — dapatkan gratis di [https://www.pexels.com/api/](https://www.pexels.com/api/)

## Instalasi

### 1. Clone / salin folder proyek

```bash
git clone <repo-url> pexels-mcp
cd pexels-mcp
```

### 2. Install dependencies & build

```bash
npm install
npm run build
```

### 3. Set API Key

Set environment variable `PEXELS_API_KEY` sebelum menjalankan server.

**Linux/macOS:**
```bash
export PEXELS_API_KEY="API_KEY_ANDA"
```

**Windows (PowerShell):**
```powershell
$env:PEXELS_API_KEY="API_KEY_ANDA"
```

## Konfigurasi di Trae IDE

Tambahkan MCP server di pengaturan MCP Trae IDE. Gunakan perintah `node` untuk menjalankan file `dist/index.js` yang sudah di-build.

Contoh konfigurasi (JSON):

```json
{
  "mcpServers": {
    "pexels": {
      "command": "node",
      "args": ["/absolute/path/ke/pexels-mcp/dist/index.js"],
      "env": {
        "PEXELS_API_KEY": "sk_API_KEY_ANDA"
      }
    }
  }
}
```

> **Catatan:** Ganti `/absolute/path/ke/pexels-mcp` dengan lokasi absolut folder proyek di sistem Anda.

### Alternatif: install global via npm

Jika ingin menjalankan dari mana saja, install package secara global:

```bash
npm install -g .
```

Lalu konfigurasi MCP dengan command `pexels-mcp`:

```json
{
  "mcpServers": {
    "pexels": {
      "command": "pexels-mcp",
      "env": {
        "PEXELS_API_KEY": "sk-API_KEY_ANDA"
      }
    }
  }
}
```

## Penggunaan

Setelah terhubung, MCP client (mis. Trae IDE) akan otomatis mendeteksi 6 tool di atas. Contoh pemanggilan:

- **Cari foto "nature"**: `search_photos` dengan `query: "nature"`
- **Cari video "city"**: `search_videos` dengan `query: "city"`
- **Detail foto**: `get_photo` dengan `id: 12377231`
- **Foto curated**: `get_curated_photos` dengan `per_page: 20`

## Struktur Proyek

```
pexels-mcp/
├── src/
│   └── index.ts        # Kode sumber MCP server
├── dist/               # Hasil build (dijalankan oleh MCP client)
├── package.json
├── tsconfig.json
└── README.md
```

## Lisensi

MIT
