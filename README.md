<div align="center">

<!-- ═══════════════════ HERO ═══════════════════ -->
<img src="https://readme-typing-svg.demolab.com/?font=IBM+Plex+Mono:wght@600&size=44&duration=2800&pause=1000&color=38BDF8&center=true&vCenter=true&width=720&lines=%F0%9D%90%93%F0%9D%90%AB%F0%9D%90%9A%F0%9D%90%9C%F0%9D%90%A4%F0%9D%90%9E%F0%9D%90%AD" alt="Tracket" />

<img src="https://readme-typing-svg.demolab.com/?font=IBM+Plex+Sans+Condensed&size=20&duration=3200&pause=900&color=0284C7&center=true&vCenter=true&width=820&lines=Sistem+Manajemen+Bengkel+Servis+%26+Garansi;Check-in+%E2%86%92+Diagnosa+%E2%86%92+Pengerjaan+%E2%86%92+Garansi+Aktif" alt="tagline" />

<br/>

![Laravel](https://img.shields.io/badge/Laravel_12-1E3A5F?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP_%E2%89%A58.2-2F6690?style=flat-square&logo=php&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind_4-3E6E96?style=flat-square&logo=tailwindcss&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-4A7FA5?style=flat-square&logo=vite&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL|SQLite-5F7080?style=flat-square&logo=mysql&logoColor=white)
![Tests](https://img.shields.io/badge/tests-passing-1B7A4B?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-1A2530?style=flat-square)

<br/>

**Dari nota kertas di meja kasir sampai kartu garansi digital di HP pelanggan, dalam satu alur.**

[✨ Fitur](#-fitur-utama) · [🚀 Instalasi 2 Menit](#-instalasi-cepat) · [🖼️ Tampilan](#-tampilan) · [🧠 Algoritma Kunci](#-algoritma-kunci) · [📜 Struktur](#-struktur-proyek)

</div>

---

## 📖 Cerita di Balik Tracket

Bengkel servis sering mengelola pekerjaan dengan kertas: nota karbon, catatan diagnosa di papan tulis, stok part di kepala teknisi. Ketika pelanggan menelepon untuk tanya *"servis saya sudah selesai?"*, orang di meja kasir harus menggali tumpukan nota.

Tracket mengubah alur itu menjadi satu sistem dengan **antarmuka terang, aksen hijau dan font Inter**: nomor nota `SRV-YYYYMM-XXXX`, cap stempel status, dan portal pelacakan yang bisa dibuka pelanggan dari HP tanpa install apa pun.

---

## ✨ Fitur Utama

<table>
<tr><td width="50%" valign="top">

### 🏪 Untuk Meja Kasir
- **Check-in cepat** dengan search-as-you-type: ketik nomor HP, identitas pelanggan lama terisi sendiri (AJAX)
- Nomor nota otomatis `SRV-YYYYMM-XXXX` yang **kebal tabrakan antrian** (transaction + row lock)
- Cetak **Tanda Terima** dan **Faktur + Kartu Garansi** langsung dari browser
- Peringatan stok kritis di dashboard sebelum teknisi baru sadar part habis

</td><td width="50%" valign="top">

### 🔧 Untuk Teknisi
- Alur status **satu-klik**: setiap tombol punya dialog konfirmasi yang menjelaskan konsekuensinya
- Pasang/lepas suku cadang, stok bergerak sendiri, tagihan terhitung ulang real-time
- Part yang habis **tidak disembunyikan** — tampil dengan label `STOK HABIS, minta admin restock`
- Filter "Hanya Tugas Saya" supaya tidak bingung dengan antrian orang lain

</td></tr>
<tr><td width="50%" valign="top">

### 🛡️ Garansi & Keuangan
- Checkout tunai atau QR dengan konfirmasi manual; status LUNAS dan waktu bayar tercetak di faktur
- Aktivasi garansi preset 30/60/90 hari dengan tanggal kedaluwarsa otomatis
- **Hitung mundur sisa hari garansi** di portal pelanggan, berubah status sendiri saat habis
- Tarif jasa selalu dibaca dari data tersimpan — tidak ada angka basi di faktur
- Batal servis = stok kembali otomatis, **dengan laporan rinci** part apa saja yang dikembalikan

</td><td width="50%" valign="top">

### 📱 Untuk Pelanggan
- Buka **/track** dari HP, tanpa login, tanpa registrasi
- Ketik nomor nota **atau** nomor WhatsApp — dua-duanya jalan
- Timeline pengerjaan 5 tahap dengan penanda hijau
- Nyalakan notifikasi WhatsApp bengkel dalam satu ketukan

</td></tr>
</table>

---

## 🚀 Instalasi Cepat

<details open>
<summary><b>⚡ Jalankan dalam 2 menit</b></summary>

```bash
# 1. Ambil proyek
git clone https://github.com/zucksfox/Tracket.git
cd Tracket

# 2. Dependensi PHP + key
composer install
cp .env.example .env            # Windows: copy .env.example .env
php artisan key:generate

# 3. Database (paling cepat: SQLite, tanpa XAMPP)
#    .env sudah default MySQL — untuk SQLite cukup ubah satu baris:
#    DB_CONNECTION=sqlite   (lalu komentari baris DB_HOST dst)
touch database/database.sqlite   # Windows PS: New-Item database\database.sqlite

# 4. Tabel + data contoh (instalasi baru; backup dahulu untuk database yang sudah berisi data)
php artisan migrate
php artisan db:seed

# 5. Frontend (Tailwind 4 via Vite, tanpa CDN)
npm install && npm run build

# 6. Hidupkan!
php artisan serve
```

Buka **http://127.0.0.1:8000** → login → dashboard. Portal pelanggan ada di **/track**.

</details>

<details>
<summary><b>🐬 Versi MySQL (XAMPP)</b></summary>

```bash
# XAMPP Control Panel → Start MySQL
# Buat database "servicetrack" via phpMyAdmin atau:
mysql -u root -e "CREATE DATABASE servicetrack"
```

`.env` (sudah seperti ini secara default):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=servicetrack
DB_USERNAME=root
DB_PASSWORD=
```
Lalu lanjut langkah 4-6 di atas.

</details>

<details>
<summary><b>📋 Prasyarat</b></summary>

| Tool | Versi | Cek |
|---|---|---|
| PHP | ≥ 8.2 | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | ≥ 20 | `node -v` |

Ekstensi PHP: `pdo_mysql` (atau `pdo_sqlite`), `mbstring`, `openssl`, `fileinfo` — semuanya aktif by default di XAMPP.

</details>

### 🔑 Akun Bawaan

| Peran | Email | Password | Bisa apa |
|---|---|---|---|
| **Admin** | `admin@tracket.test` | `password` | Pengerjaan, master data, laporan dan kelola akun |
| **Kasir** | `kasir@tracket.test` | `password` | Check-in, pembayaran tunai/QR manual, garansi dan cetak |

> Halaman login punya tombol **demo Admin/Kasir**. Akun teknisi lama tetap kompatibel tetapi tidak dibuat pada demo baru. Seeder tidak mengganti kredensial atau stok lama, dan tidak menambah transaksi demo jika sudah ada servis. Tombol demo melewati kata sandi: aplikasi ini untuk demo lokal; nonaktifkan rute quick-login sebelum dipublikasikan. Panduan lengkap: [PANDUAN_DEMO.md](PANDUAN_DEMO.md).

---

## 🖼️ Tampilan

<div align="center">

```text
╔══ DASHBOARD OPERASIONAL ═══════════════════════════════╗
║  ANTRIAN     DIKERJAKAN    SIAP AMBIL    SELESAI       ║
║     3             2              1            14       ║
║ ────────────────────────────────────────────────────── ║
║  ⚠ 2 suku cadang stok kritis (≤ 2 unit)                ║
║ ────────────────────────────────────────────────────── ║
║  SRV-202609-0004   Laptop ASUS TUF   [ SEDANG DIKERJAKAN ] ║
║  SRV-202609-0002   iPhone 13         [ SIAP DIAMBIL ]      ║
╚════════════════════════════════════════════════════════╝
```

**Bahasa desain: dashboard terang dengan aksen hijau.** Sidebar tunggal, kartu metrik,
badge status, angka tabular dan font Inter mengikuti `design.md`. Kode servis tetap monospace.

</div>

| | |
|---|---|
| **Cap stempel status** | setiap status dirender sebagai cap miring ala tanda terima fisik |
| **Timeline vertikal** | 5 tahap pengerjaan dengan titik bertinta, status aktif menyala |
| **Dokumen cetak** | tanda terima & faktur+garansi, `@media print` bersih tanpa navbar |
| **Portal HP-first** | /track dirancang dibuka dari HP dengan koneksi lambat (font & CSS lokal, 0 CDN) |

---

## 🧠 Algoritma Kunci

<details open>
<summary><b>🔢 Nomor nota sequential yang kebal tabrakan</b></summary>

```php
// Dua kasir klik simpan di detik yang sama → tidak ada nomor ganda.
DB::transaction(function () {
    $prefix  = 'SRV-' . date('Ym') . '-';
    $latest  = self::where('code', 'LIKE', "{$prefix}%")
        ->lockForUpdate()          // kunci baris → antrian di level DB
        ->orderBy('code', 'desc')
        ->first();
    // ... generate $prefix . str_pad($next, 4, '0', STR_PAD_LEFT)
});
```
</details>

<details>
<summary><b>📦 Stok part: transaksional & bisa dibalik</b></summary>

- Pasang part: kunci baris part → validasi `stock >= qty` (tolak dengan pesan yang menyebut sisa stok) → kurangi stok → catat ke tabel pivot → hitung ulang tagihan. Semua dalam satu transaksi; gagal di tengah = tidak ada perubahan setengah jadi.
- Lepas part / batal servis: stok dikembalikan + flash message menyebut **rinciannya**: *"Dikembalikan ke stok: Baterai (2 unit), LCD (1 unit)."*
</details>

<details>
<summary><b>🛡️ Garansi: dihitung sekali, dipantau selamanya</b></summary>

Saat checkout: `warranty_expires_at = today + warranty_days`.
Portal pelanggan menghitung sisa hari **dinamis** setiap kali dibuka — status "Garansi Aktif" berganti sendiri menjadi "Masa Garansi Habis" tanpa cron job.
</details>

<details>
<summary><b>🔐 Anti-kebocoran data di portal publik</b></summary>

Lookup nomor HP memakai **exact match** (menerima `0812…`, `+62-812…`, `62812…` — bukan LIKE), sehingga mengetik fragmen angka tidak pernah membuka data pelanggan lain. Nomor HP juga dinormalisasi server-side saat check-in, jadi `0812-345` dan `+6281234567890` tetap dikenali sebagai pelanggan yang sama.
</details>

---

## 📜 Struktur Proyek

```text
Tracket/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php          # login, logout, 1-klik demo login
│   │   ├── ServiceOrderController.php  # jantung: alur status, stok, checkout, garansi
│   │   ├── TechnicianController.php    # kelola akun (admin only)
│   │   ├── CustomerController.php      # master pelanggan + lookup AJAX
│   │   ├── SparepartController.php     # master part
│   │   └── TrackingController.php      # portal publik tanpa login
│   ├── Models/
│   │   ├── ServiceOrder.php            # status_meta (cap stempel), garansi, next_action
│   │   ├── Customer.php / Sparepart.php / ServiceOrderPart.php / User.php
│   │   └── Concerns/LogsActivity.php   # jejak audit (relasi polymorphic)
│   ├── Contracts/
│   │   └── ExportableReport.php        # antarmuka format ekspor
│   ├── Exports/
│   │   ├── ReportColumnMap.php         # susunan kolom (array 2 dimensi)
│   │   ├── ReportExportService.php     # baca data bertahap (chunk / do-while)
│   │   ├── ExportArchiver.php          # tulis & baca berkas di media penyimpanan
│   │   ├── ReportExporterRegistry.php  # daftar format yang tersedia
│   │   └── CsvReportExporter.php / JsonReportExporter.php  # 2 implementasi 1 antarmuka
│   └── Actions/
│       ├── DateRangeFilter.php         # validasi rentang tanggal laporan
│       └── ReportQuery.php             # satu sumber filter untuk layar + ekspor
├── resources/views/
│   ├── layouts/theme.blade.php         # design token: terang, hijau, Inter
│   ├── services/                       # daftar, detail, check-in, tanda terima, faktur
│   ├── tracking/                       # portal publik: cari, timeline, multi-perangkat
│   ├── technicians/ customers/ spareparts/ dashboard/ reports/ errors/
├── database/migrations/                # 6 tabel + FK constraint + index + soft delete
├── database/seeders/                   # data contoh realistis
├── public/fonts/                       # Inter & font kode self-hosted (demo offline)
├── tests/                              # 138 tes: php artisan test
├── pint.json                           # pedoman gaya kode (PSR-12 / preset Laravel)
├── RANCANGAN.md                        # rancangan, ERD, pemetaan ketentuan ujian
└── design.md                           # sumber kebenaran sistem desain
```

**6 tabel berelasi** — `users`, `customers`, `spareparts`, `service_orders`, `service_order_parts`, `activity_logs` — dengan foreign key (`cascadeOnDelete` untuk item, `nullOnDelete` untuk penanggung jawab, `restrictOnDelete` untuk suku cadang yang sudah dipakai) dan index pada kolom pencarian.

## 📤 Ekspor Laporan ke Berkas

Halaman **Laporan** (admin) dapat menyimpan laporan periode terpilih menjadi berkas nyata di server, lalu mengunduhnya kembali:

- Format **CSV** (siap dibuka di Excel, BOM UTF-8, pemisah titik koma) dan **JSON** (satu objek per baris dengan nama kolom sebagai kunci).
- Berkas disimpan di `storage/app/private/exports/` dengan cap waktu pada namanya, dan daftar arsipnya tampil di halaman Laporan untuk diunduh ulang.
- Isi berkas selalu identik dengan tabel di layar karena keduanya membaca filter dari `App\Actions\ReportQuery` yang sama.
- Rincian teknis: `RANCANGAN.md` bagian g–h.

---

## 🧪 Menjalankan Test

```bash
php artisan test          # 138 tes, 499 asersi
php vendor/bin/pint --test # pemeriksaan gaya kode (PSR-12)
```

Rancangan, diagram relasi basis data, dan pemetaan setiap ketentuan ujian
praktik ke kode ada di [RANCANGAN.md](RANCANGAN.md).

---

## 🛠️ Troubleshooting Singkat

| Gejala | Solusi |
|---|---|
| `could not find driver` | Aktifkan `extension=pdo_mysql` / `pdo_sqlite` di `php.ini`, restart Apache |
| `Connection refused [2002]` | MySQL belum Start di XAMPP |
| `No application encryption key` | `php artisan key:generate` |
| Halaman pola tanpa styling | `npm install && npm run build` |
| `419 Page Expired` saat submit | Sesi habis, login ulang — halaman 419 kini punya panduan 3 langkah |
| Port 8000 terpakai | `php artisan serve --port=8080` |

---

<div align="center">

**Tracket** — nota kertas yang belajar jadi sistem.
`SRV-202609-0001` · dibangun dengan Laravel · operasional bengkel dalam satu aplikasi

</div>
