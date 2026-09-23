<div align="center">

<!-- ═══════════════════ HERO ═══════════════════ -->
<img src="https://readme-typing-svg.demolab.com/?font=IBM+Plex+Mono:wght@600&size=44&duration=2800&pause=1000&color=2F6690&center=true&vCenter=true&width=720&lines=%F0%9D%90%93%F0%9D%90%AB%F0%9D%90%9A%F0%9D%90%9C%F0%9D%90%A4%F0%9D%90%9E%F0%9D%90%AD" alt="Tracket" />

<img src="https://readme-typing-svg.demolab.com/?font=IBM+Plex+Sans+Condensed&size=20&duration=3200&pause=900&color=1E3A5F&center=true&vCenter=true&width=820&lines=Sistem+Manajemen+Bengkel+Servis+%26+Garansi;Check-in+%E2%86%92+Diagnosa+%E2%86%92+Pengerjaan+%E2%86%92+Garansi+Aktif" alt="tagline" />

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

Tracket mengubah alur itu menjadi satu sistem dengan **bahasa visual kertas tanda terima & tinta biru**: nomor nota `SRV-YYYYMM-XXXX`, cap stempel status, dan portal pelacakan yang bisa dibuka pelanggan dari HP tanpa install apa pun.

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
- Aktivasi garansi preset 30/60/90 hari dengan tanggal kedaluwarsa otomatis
- **Hitung mundur sisa hari garansi** di portal pelanggan, berubah status sendiri saat habis
- Tarif jasa selalu dibaca dari data tersimpan — tidak ada angka basi di faktur
- Batal servis = stok kembali otomatis, **dengan laporan rinci** part apa saja yang dikembalikan

</td><td width="50%" valign="top">

### 📱 Untuk Pelanggan
- Buka **/track** dari HP, tanpa login, tanpa registrasi
- Ketik nomor nota **atau** nomor WhatsApp — dua-duanya jalan
- Timeline pengerjaan 5 tahap dengan titik bertinta biru
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

# 4. Tabel + data contoh
php artisan migrate:fresh --seed

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
| **Admin / Kasir** | `admin@tracket.test` | `password` | Semuanya + master data + kelola akun |
| **Teknisi** | `teknisi@tracket.test` | `password` | Pengerjaan, suku cadang, catatan teknis |

> Halaman login juga punya tombol **akun demo 1-klik**. Menu kelola akun hanya muncul untuk admin — teknisi yang memaksa masuk `/technicians` ditolak dengan 403.

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

**Bahasa desain: kertas tanda terima + tinta biru.** Bukan template dashboard generik —
setiap status adalah "cap stempel", angka penting ditulis dalam mono seperti anotasi
gambar teknik, dan border tipis meniru garis penggaris di kertas gambar.

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
│   └── Models/
│       ├── ServiceOrder.php            # status_meta (cap stempel), garansi, next_action
│       ├── Customer.php / Sparepart.php / ServiceOrderPart.php / User.php
├── resources/views/
│   ├── layouts/theme.blade.php         # design token: kertas & tinta biru (sumber tunggal)
│   ├── services/                       # daftar, detail, check-in, tanda terima, faktur
│   ├── tracking/                       # portal publik: cari, timeline, multi-perangkat
│   ├── technicians/ customers/ spareparts/ dashboard/ errors/
├── database/migrations/                # 5 tabel + FK constraint + index
├── database/seeders/                   # data contoh realistis
├── public/fonts/                       # IBM Plex self-hosted (demo jalan offline)
└── tests/                              # php artisan test
```

**5 tabel berelasi** — `users`, `customers`, `spareparts`, `service_orders`, `service_order_parts` — dengan foreign key (`cascadeOnDelete` untuk item, `nullOnDelete` untuk penanggung jawab) dan index pada kolom pencarian.

---

## 🧪 Menjalankan Test

```bash
php artisan test
```

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
`SRV-202609-0001` · dibangun dengan Laravel · desain kertas & tinta biru

</div>
