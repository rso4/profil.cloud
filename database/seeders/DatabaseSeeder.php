<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\Tenant;
use App\Models\TenantPost;
use App\Models\User;
use App\Services\ImageGenerationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Template =====
        $imgBase = 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?image_size=landscape_4_3&prompt=';
        $templates = [
            // Hotel (10)
            ['slug' => 'hotel-01', 'name' => 'Hotel Modern', 'category' => 'hotel', 'description' => 'Template modern untuk bisnis hotel dan penginapan.', 'thumbnail' => $imgBase.urlencode('website screenshot of modern hotel homepage with hero banner, navigation bar, room cards, clean blue theme')],
            ['slug' => 'hotel-02', 'name' => 'Hotel Elegance', 'category' => 'hotel', 'description' => 'Desain elegan mewah untuk hotel berbintang.', 'thumbnail' => $imgBase.urlencode('website screenshot of luxury hotel homepage with elegant serif typography, dark hero image, gold accents')],
            ['slug' => 'hotel-03', 'name' => 'Resort Tropis', 'category' => 'hotel', 'description' => 'Tampilan segar untuk resort dan villa tropis.', 'thumbnail' => $imgBase.urlencode('website screenshot of tropical resort homepage with emerald theme, beach villa hero, palm trees')],
            ['slug' => 'hotel-04', 'name' => 'Boutique Hotel', 'category' => 'hotel', 'description' => 'Gaya artistik untuk hotel butik dan guest house.', 'thumbnail' => $imgBase.urlencode('website screenshot of boutique hotel homepage with artistic dark theme, creative layout')],
            ['slug' => 'hotel-05', 'name' => 'Business Hotel', 'category' => 'hotel', 'description' => 'Profesional untuk hotel bisnis dan korporat.', 'thumbnail' => $imgBase.urlencode('website screenshot of business hotel homepage with slate dark navbar, corporate professional design')],
            ['slug' => 'hotel-06', 'name' => 'Hotel Budget', 'category' => 'hotel', 'description' => 'Ringan dan ramah untuk hotel budget dan penginapan.', 'thumbnail' => $imgBase.urlencode('website screenshot of budget hotel homepage with gradient hero, simple clean cards, friendly design')],
            ['slug' => 'hotel-07', 'name' => 'Heritage Hotel', 'category' => 'hotel', 'description' => 'Nuansa klasik untuk hotel heritage dan sejarah.', 'thumbnail' => $imgBase.urlencode('website screenshot of heritage hotel homepage with amber vintage theme, colonial architecture hero')],
            ['slug' => 'hotel-08', 'name' => 'Boutique & Spa', 'category' => 'hotel', 'description' => 'Mewah santai untuk hotel spa dan wellness.', 'thumbnail' => $imgBase.urlencode('website screenshot of spa hotel homepage with soft lighting, wellness theme, minimal elegant design')],
            ['slug' => 'hotel-09', 'name' => 'City Hotel', 'category' => 'hotel', 'description' => 'Modern minimalis untuk hotel di pusat kota.', 'thumbnail' => $imgBase.urlencode('website screenshot of city hotel homepage with urban skyline hero, modern minimalist design')],
            ['slug' => 'hotel-10', 'name' => 'Boutique Resort', 'category' => 'hotel', 'description' => 'Premium resort dengan gaya kontemporer.', 'thumbnail' => $imgBase.urlencode('website screenshot of luxury boutique resort homepage with infinity pool hero, contemporary serif design')],
            // Sekolah (10)
            ['slug' => 'school-01', 'name' => 'Sekolah Dasar', 'category' => 'school', 'description' => 'Template untuk institusi pendidikan dan sekolah.', 'thumbnail' => $imgBase.urlencode('website screenshot of elementary school homepage with blue theme, students in uniform hero, navigation bar')],
            ['slug' => 'school-02', 'name' => 'Sekolah Modern', 'category' => 'school', 'description' => 'Desain segar untuk sekolah modern.', 'thumbnail' => $imgBase.urlencode('website screenshot of modern school homepage with dark hero, clean cards, tech-focused design')],
            ['slug' => 'school-03', 'name' => 'Sekolah Islam', 'category' => 'school', 'description' => 'Nuansa islami untuk madrasah dan pesantren.', 'thumbnail' => $imgBase.urlencode('website screenshot of Islamic school homepage with emerald green theme, mosque hero, Arabic calligraphy accent')],
            ['slug' => 'school-04', 'name' => 'Kampus Prestasi', 'category' => 'school', 'description' => 'Untuk sekolah berprestasi dan program unggulan.', 'thumbnail' => $imgBase.urlencode('website screenshot of achievement school homepage with amber gold theme, trophy icons, celebration design')],
            ['slug' => 'school-05', 'name' => 'PAUD & TK', 'category' => 'school', 'description' => 'Cerian untuk PAUD, TK, dan playgroup.', 'thumbnail' => $imgBase.urlencode('website screenshot of kindergarten homepage with pink playful theme, colorful icons, happy children')],
            ['slug' => 'school-06', 'name' => 'Boarding School', 'category' => 'school', 'description' => 'Untuk sekolah berasrama dan asrama siswa.', 'thumbnail' => $imgBase.urlencode('website screenshot of boarding school homepage with indigo dark theme, campus dormitory hero')],
            ['slug' => 'school-07', 'name' => 'Sekolah Teknik', 'category' => 'school', 'description' => 'Profesional untuk SMK dan kejuruan.', 'thumbnail' => $imgBase.urlencode('website screenshot of vocational school homepage with slate dark navbar, orange accents, workshop hero')],
            ['slug' => 'school-08', 'name' => 'Pesantren', 'category' => 'school', 'description' => 'Tradisional modern untuk pesantren.', 'thumbnail' => $imgBase.urlencode('website screenshot of Islamic boarding school homepage with teal green theme, traditional modern design')],
            ['slug' => 'school-09', 'name' => 'Kursus & Bimbel', 'category' => 'school', 'description' => 'Untuk lembaga kursus dan bimbingan belajar.', 'thumbnail' => $imgBase.urlencode('website screenshot of tutoring center homepage with purple theme, study icons, bright classroom')],
            ['slug' => 'school-10', 'name' => 'Sekolah Luar Biasa', 'category' => 'school', 'description' => 'Ramah dan inklusif untuk pendidikan khusus.', 'thumbnail' => $imgBase.urlencode('website screenshot of special education school homepage with cyan inclusive theme, welcoming supportive design')],
            // SME (10)
            ['slug' => 'sme-01', 'name' => 'UMKM Kreatif', 'category' => 'sme', 'description' => 'Template kreatif untuk usaha mikro, kecil, dan menengah.', 'thumbnail' => $imgBase.urlencode('website screenshot of creative small business homepage with gradient hero, product cards, rounded design')],
            ['slug' => 'sme-02', 'name' => 'Bisnis Modern', 'category' => 'sme', 'description' => 'Desain bersih dan modern untuk bisnis profesional.', 'thumbnail' => $imgBase.urlencode('website screenshot of modern business homepage with gray dark hero, professional team image, service cards')],
            ['slug' => 'sme-03', 'name' => 'Kuliner & Resto', 'category' => 'sme', 'description' => 'Tampilan menggugah selera untuk restoran dan kuliner.', 'thumbnail' => $imgBase.urlencode('website screenshot of restaurant homepage with red theme, food photography hero, menu cards')],
            ['slug' => 'sme-04', 'name' => 'Fashion & Boutique', 'category' => 'sme', 'description' => 'Gaya elegan untuk butik dan brand fashion.', 'thumbnail' => $imgBase.urlencode('website screenshot of fashion boutique homepage with elegant serif, dark theme, clothing display hero')],
            ['slug' => 'sme-05', 'name' => 'Kopi & Kafe', 'category' => 'sme', 'description' => 'Nuansa hangat untuk kafe dan kedai kopi.', 'thumbnail' => $imgBase.urlencode('website screenshot of coffee shop homepage with amber brown theme, barista latte art hero, warm cozy design')],
            ['slug' => 'sme-06', 'name' => 'Toko Online', 'category' => 'sme', 'description' => 'Clean e-commerce untuk toko online.', 'thumbnail' => $imgBase.urlencode('website screenshot of online store homepage with blue theme, product grid, ecommerce packaging hero')],
            ['slug' => 'sme-07', 'name' => 'Bisnis Jasa', 'category' => 'sme', 'description' => 'Profesional untuk perusahaan jasa dan layanan.', 'thumbnail' => $imgBase.urlencode('website screenshot of service business homepage with slate dark navbar, professional handshake hero, service cards')],
            ['slug' => 'sme-08', 'name' => 'Spa & Kecantikan', 'category' => 'sme', 'description' => 'Mewah santai untuk spa dan salon kecantikan.', 'thumbnail' => $imgBase.urlencode('website screenshot of beauty spa homepage with pink rose theme, spa interior hero, elegant serif design')],
            ['slug' => 'sme-09', 'name' => 'Event & Wedding', 'category' => 'sme', 'description' => 'Elegan untuk wedding organizer dan event.', 'thumbnail' => $imgBase.urlencode('website screenshot of wedding organizer homepage with rose elegant theme, floral decoration hero, serif typography')],
            ['slug' => 'sme-10', 'name' => 'Craft & Kerajinan', 'category' => 'sme', 'description' => 'Hangat untuk usaha kerajinan dan handmade.', 'thumbnail' => $imgBase.urlencode('website screenshot of handmade craft homepage with amber warm theme, artisan workshop hero, rustic design')],
        ];

        foreach ($templates as $t) {
            Template::updateOrCreate(['slug' => $t['slug']], $t);
        }

        // ===== Super Admin =====
        User::updateOrCreate(
            ['email' => 'admin@profil.cloud'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@2026'),
                'role' => 'super_admin',
                'tenant_id' => null,
            ]
        );

        // ===== Demo Tenant: Hotel =====
        $hotel = Tenant::updateOrCreate(
            ['slug' => 'hotel'],
            [
                'name' => 'Grand Hotel Makassar',
                'email' => 'info@grandhotel.com',
                'phone' => '+62 411 123456',
                'address' => 'Jl. Jend. Sudirman No. 1, Makassar',
                'template_slug' => 'hotel-01',
                'primary_color' => '#b45309',
                'secondary_color' => '#1e293b',
                'is_active' => true,
            ]
        );
        $hotel->profile()->updateOrCreate(['tenant_id' => $hotel->id], [
            'tagline' => 'Pengalaman menginap mewah di jantung kota Makassar',
            'description' => 'Grand Hotel Makassar menawarkan kamar mewah, restoran premium, dan fasilitas lengkap untuk kenyamanan Anda.',
            'website' => 'https://grandhotel.com',
            'instagram' => '@grandhotelmks',
        ]);
        $hotel->services()->delete();
        $hotel->services()->createMany([
            ['name' => 'Kamar Deluxe', 'description' => 'Kamar luas dengan pemandangan kota', 'icon' => '🛏️', 'price' => 850000],
            ['name' => 'Restoran', 'description' => 'Menu internasional dan lokal', 'icon' => '🍽️', 'price' => 150000],
            ['name' => 'Kolam Renang', 'description' => 'Kolam renang outdoor dengan bar', 'icon' => '🏊', 'price' => 100000],
        ]);
        $hotel->contacts()->delete();
        $hotel->contacts()->createMany([
            ['label' => 'Telepon', 'value' => '+62 411 123456', 'type' => 'phone'],
            ['label' => 'Email', 'value' => 'info@grandhotel.com', 'type' => 'email'],
            ['label' => 'Alamat', 'value' => 'Jl. Jend. Sudirman No. 1, Makassar', 'type' => 'address'],
        ]);
        $this->seedGallery($hotel, [
            'Lobby Utama', 'Kamar Deluxe', 'Restoran Anggrek', 'Kolam Renang Rooftop',
        ]);
        $hotelAdmin = User::updateOrCreate(
            ['email' => 'hotel@profil.cloud'],
            [
                'name' => 'Admin Hotel',
                'password' => Hash::make('Hotel@2026'),
                'role' => 'tenant_admin',
                'tenant_id' => $hotel->id,
            ]
        );
        $this->seedCmsContent($hotel, $hotelAdmin, [
            'categories' => [
                ['name' => 'Berita & Event', 'description' => 'Berita dan acara terbaru dari Grand Hotel Makassar.'],
                ['name' => 'Promosi & Paket', 'description' => 'Paket menginap dan penawaran spesial.'],
                ['name' => 'Tips & Inspirasi', 'description' => 'Tips perjalanan dan inspirasi liburan.'],
            ],
            'tags' => ['promo', 'staycation', 'tips', 'keluarga'],
            'media' => ['Kamar Deluxe', 'Rooftop Pool', 'Sarapan Pagi'],
            'posts' => [
                [
                    'title' => 'Grand Opening Rooftop Pool Bar Skyline',
                    'excerpt' => 'Rooftop pool bar terbaru kami resmi dibuka! Nikmati sunset Makassar dengan cocktail signature dan live music setiap Jumat & Sabtu.',
                    'content' => '<p>Grand Hotel Makassar dengan bangga mengumumkan pembukaan <strong>Rooftop Pool Bar Skyline</strong> di lantai teratas hotel.</p><h2>Apa yang Bisa Anda Nikmati?</h2><ul><li>Kolam renang infinity dengan pemandangan kota</li><li>Signature cocktail karya bartender kami</li><li>Live music akustik setiap Jumat dan Sabtu</li></ul><blockquote>Sunset terbaik di Makassar hanya bisa dinikmati dari sini.</blockquote><p>Grand opening berlangsung hingga akhir bulan dengan diskon 20% untuk semua minuman. Sampai jumpa di atas!</p>',
                    'category' => 'Promosi & Paket',
                    'cover' => 'Rooftop Pool',
                    'tags' => ['promo', 'staycation'],
                    'status' => 'published',
                    'published_at' => now()->subDays(5),
                    'featured' => true,
                    'views' => 128,
                ],
                [
                    'title' => 'Paket Staycation Keluarga Akhir Pekan',
                    'excerpt' => 'Paket menginap 2 hari 1 malam termasuk sarapan untuk 2 dewasa dan 2 anak, plus akses kolam renang.',
                    'content' => '<p>Liburan singkat bersama keluarga kini lebih hemat! Paket <strong>Staycation Keluarga</strong> kami mencakup:</p><ul><li>Kamar Deluxe untuk 2 dewasa + 2 anak</li><li>Sarapan prasmanan untuk 4 orang</li><li>Akses kolam renang dan gym</li><li>Late check-out hingga pukul 14.00</li></ul><p>Promo berlaku setiap Jumat hingga Minggu. <em>Slots terbatas, segera pesan!</em></p>',
                    'category' => 'Promosi & Paket',
                    'cover' => 'Kamar Deluxe',
                    'tags' => ['staycation', 'promo', 'keluarga'],
                    'status' => 'published',
                    'published_at' => now()->subDays(12),
                    'views' => 87,
                ],
                [
                    'title' => '5 Tips Memilih Kamar Hotel untuk Liburan Keluarga',
                    'excerpt' => 'Ukuran kamar, fasilitas anak, hingga lokasi — ini yang perlu Anda perhatikan sebelum memesan kamar hotel.',
                    'content' => '<p>Memesan kamar hotel untuk liburan keluarga punya tantangan tersendiri. Berikut 5 tips dari tim kami:</p><h2>1. Perhatikan Ukuran Kamar</h2><p>Kamar minimal 28 m² agar nyaman untuk keluarga kecil.</p><h2>2. Cek Fasilitas untuk Anak</h2><p>Kolam renang anak, high chair, dan menu khusus anak sangat membantu.</p><h2>3. Pilih Lokasi Strategis</h2><p>Dekat destinasi wisata menghemat waktu dan biaya transportasi.</p><h2>4. Bandingkan Paket</h2><p>Sering kali paket dengan sarapan lebih murah daripada membeli terpisah.</p><h2>5. Baca Ulasan Terbaru</h2><p>Ulasan 3 bulan terakhir paling mencerminkan kondisi hotel saat ini.</p>',
                    'category' => 'Tips & Inspirasi',
                    'tags' => ['tips', 'keluarga'],
                    'status' => 'published',
                    'published_at' => now()->subDays(20),
                    'views' => 43,
                ],
                [
                    'title' => 'Menu Baru Restoran Anggrek: Rasa Nusantara',
                    'excerpt' => 'Draf artikel menu baru Restoran Anggrek.',
                    'content' => '<p>Restoran Anggrek memperkenalkan menu baru dengan sentuhan rempah Nusantara. Daftar menu lengkap akan diumumkan setelah finalisasi chef.</p>',
                    'category' => 'Berita & Event',
                    'cover' => 'Sarapan Pagi',
                    'tags' => ['kuliner'],
                    'status' => 'draft',
                ],
                [
                    'title' => 'Promo Ramadan: Diskon 20% Kamar Deluxe',
                    'excerpt' => 'Penawaran spesial menyambut bulan Ramadan — diskon 20% untuk semua tipe Kamar Deluxe.',
                    'content' => '<p>Menyambut bulan Ramadan, Grand Hotel Makassar memberikan diskon 20% untuk semua Kamar Deluxe. Termasuk sahur dan buka puasa untuk 2 orang.</p>',
                    'category' => 'Promosi & Paket',
                    'tags' => ['promo'],
                    'status' => 'scheduled',
                    'published_at' => now()->addDays(10),
                ],
            ],
        ]);

        // ===== Demo Tenant: UMKM =====
        $sme = Tenant::updateOrCreate(
            ['slug' => 'umkm'],
            [
                'name' => 'Kopi Nusantara',
                'email' => 'halo@kopinusantara.id',
                'phone' => '+62 812 345678',
                'address' => 'Jl. Somba Opu No. 45, Makassar',
                'template_slug' => 'sme-01',
                'primary_color' => '#7c3aed',
                'secondary_color' => '#111827',
                'is_active' => true,
            ]
        );
        $sme->profile()->updateOrCreate(['tenant_id' => $sme->id], [
            'tagline' => 'Kopi lokal berkualitas dari petani Nusantara',
            'description' => 'Kopi Nusantara menyajikan biji kopi pilihan dari berbagai daerah di Indonesia, diolah dengan penuh cinta.',
            'website' => 'https://kopinusantara.id',
            'instagram' => '@kopinusantara',
        ]);
        $sme->services()->delete();
        $sme->services()->createMany([
            ['name' => 'Kopi Arabika', 'description' => 'Biji kopi arabika premium', 'icon' => '☕', 'price' => 50000],
            ['name' => 'Kopi Robusta', 'description' => 'Biji kopi robusta kuat', 'icon' => '🫘', 'price' => 40000],
            ['name' => 'Paket Langganan', 'description' => 'Langganan kopi bulanan', 'icon' => '📦', 'price' => 200000],
        ]);
        $sme->contacts()->delete();
        $sme->contacts()->createMany([
            ['label' => 'Telepon', 'value' => '+62 812 345678', 'type' => 'phone'],
            ['label' => 'Email', 'value' => 'halo@kopinusantara.id', 'type' => 'email'],
            ['label' => 'Instagram', 'value' => '@kopinusantara', 'type' => 'social'],
        ]);
        $this->seedGallery($sme, [
            'Signature Latte', 'Biji Kopi Pilihan', 'Suasana Kafe', 'Barista Kami',
        ]);
        $smeAdmin = User::updateOrCreate(
            ['email' => 'umkm@profil.cloud'],
            [
                'name' => 'Admin UMKM',
                'password' => Hash::make('Umkm@2026'),
                'role' => 'tenant_admin',
                'tenant_id' => $sme->id,
            ]
        );
        $this->seedCmsContent($sme, $smeAdmin, [
            'categories' => [
                ['name' => 'Cerita Kopi', 'description' => 'Kisah di balik biji kopi dan para petani lokal.'],
                ['name' => 'Tips & Resep', 'description' => 'Tips menyeduh dan resep minuman kopi.'],
                ['name' => 'Promo Kafe', 'description' => 'Promo dan penawaran spesial di kedai kami.'],
            ],
            'tags' => ['kopi', 'petani-lokal', 'resep', 'promo'],
            'media' => ['Biji Kopi Pilihan', 'Latte Art', 'Suasana Kafe'],
            'posts' => [
                [
                    'title' => 'Perjalanan Biji Kopi: dari Petani hingga Cangkir Anda',
                    'excerpt' => 'Setiap tegukan kopi di kedai kami menyimpan perjalanan panjang dari kebun petani di dataran tinggi hingga cangkir Anda.',
                    'content' => '<p>Kami percaya kopi terbaik berasal dari hubungan yang adil dengan para petani. Berikut perjalanan biji kopi kami:</p><h2>1. Panen di Dataran Tinggi</h2><p>Petani mitra kami memanen cherry kopi matang penuh di ketinggian 1.200 mdpl.</p><h2>2. Proses dan Fermentasi</h2><p>Biji diproses metode full-washed, difermentasi 36 jam untuk keasaman yang bersih.</p><h2>3. Roasting di Rumah</h2><p>Kami meroving sendiri setiap minggu agar selalu segar saat disajikan.</p><blockquote>Kopi segar adalah kopi yang di-roasting kurang dari 30 hari.</blockquote><p>Datang dan rasakan perbedaannya!</p>',
                    'category' => 'Cerita Kopi',
                    'cover' => 'Biji Kopi Pilihan',
                    'tags' => ['kopi', 'petani-lokal'],
                    'status' => 'published',
                    'published_at' => now()->subDays(7),
                    'featured' => true,
                    'views' => 96,
                ],
                [
                    'title' => 'Cara Menyeduh Kopi V60 yang Benar di Rumah',
                    'excerpt' => 'Rasio, gilingan, suhu air — panduan singkat menyeduh kopi manual brew ala barista kami.',
                    'content' => '<p>V60 adalah metode seduh favorit karena hasilnya bersih dan aromatik. Ikuti langkah berikut:</p><h2>Yang Anda Butuhkan</h2><ul><li>15 gram kopi, gilingan medium</li><li>250 ml air bersuhu 92&deg;C</li><li>Filter paper V60 dan timbangan</li></ul><h2>Langkah Menyeduh</h2><ol><li>Bilas filter dengan air panas, buang airnya.</li><li>Tuang kopi, tare timbangan.</li><li>Bloom 30 ml air selama 40 detik.</li><li>Tuang sisanya melingkar hingga 250 ml dalam 2 menit.</li></ol><p>Selamat mencoba di rumah!</p>',
                    'category' => 'Tips & Resep',
                    'cover' => 'Latte Art',
                    'tags' => ['resep', 'kopi'],
                    'status' => 'published',
                    'published_at' => now()->subDays(15),
                    'views' => 64,
                ],
                [
                    'title' => 'Promo Buy 1 Get 1 Setiap Akhir Pekan',
                    'excerpt' => 'Setiap Sabtu & Minggu, beli satu kopi signature gratis satu untuk teman Anda.',
                    'content' => '<p>Waktunya quality time! Setiap Sabtu dan Minggu, semua <strong>kopi signature</strong> kami Buy 1 Get 1. Berlaku untuk dine-in di kedai hingga pukul 17.00.</p>',
                    'category' => 'Promo Kafe',
                    'tags' => ['promo'],
                    'status' => 'published',
                    'published_at' => now()->subDays(25),
                    'views' => 51,
                ],
                [
                    'title' => 'Di Balik Suasana Hangat Kafe Kami',
                    'excerpt' => 'Draf artikel tentang konsep interior kedai.',
                    'content' => '<p>Kami merancang kedai dengan pencahayaan hangat dan kayu lokal agar setiap tamu merasa seperti di rumah sendiri. Cerita lengkap menyusul.</p>',
                    'category' => 'Cerita Kopi',
                    'cover' => 'Suasana Kafe',
                    'tags' => [],
                    'status' => 'draft',
                ],
                [
                    'title' => 'Kelas Barista Dasar untuk Pemula',
                    'excerpt' => 'Belajar dasar ekstraksi espresso dan latte art bersama head barista kami.',
                    'content' => '<p>Kelas barista dasar akan dibuka bulan depan dengan kuota 10 peserta. Materi meliputi kalibrasi grinder, ekstraksi espresso, dan praktik latte art.</p>',
                    'category' => 'Tips & Resep',
                    'tags' => ['resep'],
                    'status' => 'scheduled',
                    'published_at' => now()->addDays(14),
                ],
            ],
        ]);
    }

    /**
     * Seed demo content CMS (kategori, tag, media, artikel) untuk tenant.
     *
     * Aman untuk production: jika tenant sudah memiliki artikel apa pun
     * (termasuk yang terhapus), seeding dilewati agar konten asli tidak
     * tertimpa. Kategori/tag/media bersifat idempoten (updateOrCreate).
     *
     * @param  Tenant  $tenant  Tenant pemilik konten
     * @param  User|null  $author  Admin tenant sebagai penulis artikel
     * @param  array  $spec  Struktur: categories[], tags[], media[], posts[]
     */
    private function seedCmsContent(Tenant $tenant, ?User $author, array $spec): void
    {
        $hasPosts = TenantPost::query()->where('tenant_id', $tenant->id)->withTrashed()->exists();
        if ($hasPosts) {
            return;
        }

        // Kategori (unik per tenant via slug)
        $categories = collect();
        foreach ($spec['categories'] as $category) {
            $categories[$category['name']] = $tenant->categories()->updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                ['name' => $category['name'], 'description' => $category['description'] ?? null]
            );
        }

        // Tag (unik per tenant via slug)
        $tags = collect();
        foreach ($spec['tags'] as $name) {
            $tags[$name] = $tenant->tags()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        // Media demo: artwork digenerate (teknik sama dgn seedGallery) + thumbnail
        $service = app(ImageGenerationService::class);
        $media = collect();
        foreach ($spec['media'] as $title) {
            $slug = Str::slug($title);
            $path = 'media/'.$tenant->id.'/'.$slug.'-demo.jpg';
            $thumbPath = 'media/'.$tenant->id.'/'.$slug.'-demo-thumb.jpg';

            $service->generateArtwork($path, $title, 1280, 720, 85);
            $service->createThumbnail($path, $thumbPath);

            $media[$title] = $tenant->media()->updateOrCreate(
                ['file_path' => 'storage/'.$path],
                [
                    'uploaded_by' => $author?->id,
                    'name' => $slug.'.jpg',
                    'thumb_path' => 'storage/'.$thumbPath,
                    'mime_type' => 'image/jpeg',
                    'size' => Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : 0,
                    'alt_text' => $title,
                ]
            );
        }

        // Artikel demo (published / draft / scheduled)
        foreach ($spec['posts'] as $post) {
            $created = $tenant->posts()->create([
                'author_id' => $author?->id,
                'category_id' => $categories[$post['category']]?->id ?? null,
                'cover_media_id' => isset($post['cover']) ? ($media[$post['cover']]?->id ?? null) : null,
                'title' => $post['title'],
                'slug' => Str::slug($post['title']),
                'excerpt' => $post['excerpt'] ?? null,
                // Disanitasi agar format tersimpan identik dengan jalur aplikasi
                'content' => isset($post['content']) ? sanitize_html($post['content']) : null,
                'status' => $post['status'] ?? 'draft',
                'published_at' => $post['published_at'] ?? null,
                'is_featured' => $post['featured'] ?? false,
                'views' => $post['views'] ?? 0,
                'meta_title' => $post['meta_title'] ?? null,
                'meta_description' => $post['meta_description'] ?? null,
            ]);

            $tagIds = collect($post['tags'] ?? [])
                ->filter(fn ($name) => $tags->has($name))
                ->map(fn ($name) => $tags[$name]->id);

            $created->tags()->sync($tagIds->all());
        }
    }

    /**
     * Seed galeri demo untuk tenant: generate artwork unik + thumbnail ringan
     * via intervention/image v3 (ImageGenerationService), lalu simpan record.
     *
     * @param  Tenant  $tenant  Tenant pemilik galeri
     * @param  string[]  $titles  Judul tiap item galeri
     */
    private function seedGallery(Tenant $tenant, array $titles): void
    {
        $tenant->galleries()->delete();

        $service = app(ImageGenerationService::class);
        $baseDir = 'galleries/'.$tenant->id;

        foreach ($titles as $index => $title) {
            $slug = Str::slug($title);
            $imagePath = "{$baseDir}/{$slug}.jpg";
            $thumbPath = "{$baseDir}/{$slug}-thumb.jpg";

            $service->generateArtwork($imagePath, $title, 1280, 960, 85);
            $service->createThumbnail($imagePath, $thumbPath);

            $tenant->galleries()->create([
                'title' => $title,
                'image' => 'storage/'.$imagePath,
                'thumb' => 'storage/'.$thumbPath,
                'sort_order' => $index,
            ]);
        }
    }
}
