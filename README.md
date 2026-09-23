# Tracket

Sistem manajemen bengkel servis elektronik (smartphone, laptop, & perangkat lainnya) untuk **Sertifikasi Kompetensi BNSP**, bidang Keahlian Pemrograman Web / Software Development. Mencakup penerimaan unit (check-in), alur pengerjaan teknisi, suku cadang transaksional dengan rollback stok, aktivasi garansi, dokumen cetak resmi (tanda terima & faktur + kartu garansi), serta portal pelacakan publik tanpa login.

Dibangun dengan prinsip desain "kertas tanda terima & tinta biru blueprint" — fungsional, padat informasi, tanpa elemen dekoratif generik.

---

## Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Tech Stack](#tech-stack)
3. [Prasyarat Instalasi](#prasyarat-instalasi)
4. [Instalasi Lengkap (Dari Nol)](#instalasi-lengkap-dari-nol)
5. [Instalasi Cepat (Ringkasan)](#instalasi-cepat-ringkasan)
6. [Konfigurasi Database: MySQL vs SQLite](#konfigurasi-database-mysql-vs-sqlite)
7. [Akun Demo & Login](#akun-demo--login)
8. [Menjalankan Aplikasi](#menjalankan-aplikasi)
9. [Struktur Menu & Halaman](#struktur-menu--halaman)
10. [Alur Kerja Operasional](#alur-kerja-operasional)
11. [Algoritma Penting (Untuk Pertanyaan Asesor)](#algoritma-penting-untuk-pertanyaan-asesor)
12. [Konfigurasi Kontak Bengkel](#konfigurasi-kontak-bengkel)
13. [Menjalankan Test](#menjalankan-test)
14. [Troubleshooting](#troubleshooting)
15. [Mode Demo Asesor](#mode-demo-asesor)

---

## Fitur Utama

**Backoffice (login):**
- Dashboard operasional: antrian, pengerjaan aktif, siap ambil, selesai, omzet bulan ini, peringatan stok kritis.
- Check-In servis dengan search-as-you-type (ketik nomor HP → data pelanggan lama terisi otomatis via AJAX).
- Nomor servis sequential otomatis berformat `SRV-YYYYMM-XXXX`, aman terhadap race condition (`DB::transaction` + `lockForUpdate`).
- Alur status satu-klik: Menunggu Diagnosa → Sedang Diagnosa → Sedang Dikerjakan → Siap Diambil → Selesai & Diambil, masing-masing dengan dialog konfirmasi dan "cap stempel" visual.
- Suku cadang: pasang part ke unit (stok terpotong transaksional), lepas part (stok kembali otomatis), part stok habis tampil sebagai opsi disabled berlabel "STOK HABIS, minta admin restock".
- Checkout & aktivasi garansi: preset 30/60/90 hari atau manual; tarif jasa selalu dibaca dari data tersimpan (satu sumber kebenaran).
- Pembatalan servis dengan rollback stok otomatis + laporan rinci part yang dikembalikan.
- Master data: Pelanggan, Suku Cadang, dan Teknisi/Akun Pengguna (CRUD penuh, khusus admin).
- Dokumen cetak: Tanda Terima Masuk Servis & Faktur + Kartu Garansi (siap cetak, `@media print`).
- Manajemen akun: tambah teknisi/admin baru, ubah data, reset password, hapus dengan pengaman (tidak bisa hapus akun sendiri / admin terakhir).

**Portal publik (tanpa login):**
- `/track` — pelanggan mengecek status servis dengan nomor nota ATAU nomor WhatsApp (match penuh, anti-kebocoran data pelanggan lain).
- Timeline pengerjaan 5 tahap, hitung mundur sisa hari garansi aktif, rincian komponen yang diganti, tombol WhatsApp bengkel.
- Pesan error yang menjelaskan: nomor salah ketik → instruksi periksa nota, bukan "Terjadi kesalahan".

**Keamanan:**
- CSRF protection, password Bcrypt, session database.
- Role middleware: modul master data & manajemen akun hanya untuk admin; teknisi mendapat 403.
- Normalisasi & validasi nomor HP server-side (terima `0812…`, `+62-812…`, `62812…`).
- Lookup pelanggan publik memakai exact match, bukan LIKE.

---

## Tech Stack

| Komponen | Versi/Teknologi |
|---|---|
| PHP | >= 8.2 |
| Laravel | 12.x |
| Database | MySQL (XAMPP/MariaDB) atau SQLite (fallback tanpa setup) |
| Frontend | Blade + Tailwind CSS 4 (dikompilasi lokal via Vite 7, **tanpa CDN**) |
| Font | IBM Plex Sans Condensed + IBM Plex Mono (self-hosted, jalan offline) |
| Node.js | >= 20 (untuk build Tailwind) |
| Composer | >= 2 |

---

## Prasyarat Instalasi

1. **XAMPP** (atau setara) yang memuat **PHP 8.2+** dan **MySQL/MariaDB** — unduh: https://www.apachefriends.org
2. **Composer** — unduh: https://getcomposer.org/download/
3. **Node.js 20+** dan npm — unduh: https://nodejs.org (pilih LTS)
4. (Opsional) Git untuk clone repositori.

Cek instalasi di terminal:

```bash
php -v      # harus tampil PHP >= 8.2
composer -V # harus tampil Composer 2.x
node -v     # harus tampil v20 ke atas
npm -v
```

Ekstensi PHP yang dibutuhkan (biasanya sudah aktif di XAMPP): `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `json`, `fileinfo` (atau `pdo_sqlite` bila memakai SQLite).

---

## Instalasi Lengkap (Dari Nol)

### 1. Salin proyek

```bash
# Jika dari repositori git:
git clone <url-repo> project_bnsp
cd project_bnsp

# Atau jika sudah punya folder proyek, cukup masuk ke foldernya:
# cd D:\project\project_bnsp
```

### 2. Install dependensi PHP (Laravel)

```bash
composer install
```

### 3. Buat file konfigurasi `.env`

```bash
# Windows (cmd):
copy .env.example .env

# Linux/Mac (bash):
cp .env.example .env
```

Lalu buat application key:

```bash
php artisan key:generate
```

### 4. Siapkan database

**Pilihan A, MySQL (default `.env` proyek ini):**

1. Buka **XAMPP Control Panel** → klik **Start** pada **Apache** dan **MySQL**.
2. Buat database baru bernama `servicetrack` (bisa via phpMyAdmin di `http://localhost/phpmyadmin` → New → nama `servicetrack` → Create; atau lewat command line di bawah).
3. Pastikan bagian DB di `.env` seperti ini:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=servicetrack
DB_USERNAME=root
DB_PASSWORD=
```

**Pilihan B, SQLite (tanpa XAMPP, paling cepat untuk coba-coba):**

```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

SQLite membutuhkan file database kosong:

```bash
# Windows (PowerShell):
New-Item database/database.sqlite -ItemType File

# Linux/Mac:
touch database/database.sqlite
```

> File `database/database.sqlite` sudah disertakan pada repositori ini, jadi Pilihan B biasanya bisa langsung `migrate`.

### 5. Jalankan migrasi + seeder (membuat tabel & data demo)

```bash
php artisan migrate:fresh --seed
```

Perintah ini membuat 5 tabel (`users`, `customers`, `spareparts`, `service_orders`, `service_order_parts`) beserta foreign key + index, lalu mengisi data demo: akun login, 5 pelanggan, 10 suku cadang (termasuk 1 dengan stok 0 untuk mendemonstrasikan label "STOK HABIS"), dan 6 riwayat servis lengkap dengan detail part & garansi.

### 6. Install dependensi frontend & build Tailwind

```bash
npm install
npm run build
```

Tailwind dikompilasi **lokal** ke `public/build/` — aplikasi tidak memakai CDN Tailwind, jadi tetap berjalan penuh tanpa internet saat demo.

Font (IBM Plex) sudah self-hosted di `public/fonts/` dan tidak perlu diunduh ulang.

### 7. (Hanya MySQL) Pastikan MySQL aktif

Buka XAMPP Control Panel → **MySQL** → Start. Tanpa ini aplikasi akan error "connection refused".

### 8. Jalankan server

```bash
php artisan serve
```

Aplikasi siap di **http://127.0.0.1:8000**

---

## Instalasi Cepat (Ringkasan)

Untuk mesin yang sudah punya PHP + Composer + Node:

```bash
git clone <url-repo> project_bnsp && cd project_bnsp
composer install
copy .env.example .env          # (bash: cp .env.example .env)
php artisan key:generate
# siapkan DB (lihat langkah 4 di atas), lalu:
php artisan migrate:fresh --seed
npm install && npm run build
php artisan serve
```

Atau satu perintah (memakai script bawaan `composer.json`, default SQLite):

```bash
composer setup
```

---

## Konfigurasi Database: MySQL vs SQLite

| | MySQL (XAMPP) | SQLite |
|---|---|---|
| Cocok untuk | Demo asesmen resmi / production | Uji coba cepat / laptop tanpa XAMPP |
| Setup | Buat database `servicetrack` + Start MySQL | Pastikan file `database/database.sqlite` ada |
| Kecepatan | Cepat | Cepat (untuk skala data demo) |
| `.env` | `DB_CONNECTION=mysql` + host/port | `DB_CONNECTION=sqlite`, baris lain dikomentari |

Jika mengganti koneksi, jalankan ulang `php artisan config:clear` dan `php artisan migrate:fresh --seed`.

---

## Akun Demo & Login

URL login: **http://127.0.0.1:8000/login** (tersedia juga tombol **Akses Cepat 1-Klik** di halaman login).

| Peran | Email | Password | Hak Akses |
|---|---|---|---|
| Administrator / Kasir | `admin@tracket.test` | `password` | Semua: check-in, master pelanggan/sukucadang/teknisi, checkout & garansi, laporan omzet |
| Teknisi Utama | `teknisi@tracket.test` | `password` | Antrian servis, diagnosa & update progres, pasang/lepas suku cadang, catatan teknis |

Menu **Teknisi** (manajemen akun) hanya muncul dan hanya bisa diakses oleh admin — teknisi yang membuka `/technicians` langsung mendapat **403**.

---

## Menjalankan Aplikasi

```bash
php artisan serve          # default http://127.0.0.1:8000
php artisan serve --port=8080   # jika port 8000 terpakai
```

Halaman penting:

| URL | Fungsi | Akses |
|---|---|---|
| `/` | Otomatis redirect ke portal tracking | Publik |
| `/track` | Portal pelacakan pelanggan (tanpa login) | Publik |
| `/login` | Halaman masuk backoffice | Publik |
| `/dashboard` | Dashboard operasional | Login |
| `/services` | Daftar seluruh servis + filter status | Login |
| `/service/check-in` | Form penerimaan unit baru | Admin |
| `/customers`, `/spareparts`, `/technicians` | Master data | Admin |
| `/services/{id}/print-receipt` | Tanda terima siap cetak | Login |
| `/services/{id}/print-invoice` | Faktur + kartu garansi siap cetak | Login |

---

## Struktur Menu & Halaman

```
NAVBAR (login)
├── Dashboard          : metrik harian + aktivitas servis terkini
├── Check-In Servis    : (admin) form penerimaan unit
├── Daftar Servis      : tabel semua servis, filter status, "Hanya Tugas Saya" (teknisi)
├── Pelanggan          : (admin) master pelanggan
├── Suku Cadang        : katalog & stok, stok kritis ditandai
├── Teknisi            : (admin) kelola akun teknisi/admin
└── Portal Pelanggan   : buka /track di tab baru
```

---

## Alur Kerja Operasional

### A. Check-in servis (Kasir)
1. Menu **Check-In Servis**.
2. Ketik nomor HP → bila pelanggan lama, nama & alamat terisi otomatis (badge "Pelanggan Terdaftar").
3. Isi tipe perangkat + keluhan (wajib), lengkapan & teknisi (opsional).
4. **Simpan Penerimaan Servis** → muncul Nomor Nota `SRV-YYYYMM-XXXX` → klik **Cetak Tanda Terima**.

### B. Pengerjaan (Teknisi)
1. Menu **Daftar Servis** → filter "Diagnosa" / centang "Hanya Tugas Saya".
2. Buka servis → tombol aksi satu-klik dengan konfirmasi: **Mulai Diagnosa → Mulai Pengerjaan → Tandai Siap Diambil**.
3. Pasang suku cadang: pilih part (yang habis terlabel jelas) → jumlah → **Pasang ke Unit** (stok terpotong otomatis). Salah pasang → **Lepas Part** (stok kembali).
4. Isi **Laporan Teknis** dan **Simpan Perubahan**.

### C. Checkout & garansi (Kasir/Admin)
1. Saat status **Siap Diambil**, panel "Penyerahan Unit & Garansi" aktif.
2. Pilih preset **30 / 60 / 90 hari** (atau isi manual).
3. **Konfirmasi Unit Diambil Pelanggan** (ada dialog konfirmasi; status menjadi Selesai, permanen).
4. **Cetak Faktur & Garansi** → kartu jaminan resmi dengan tanggal kedaluwarsa otomatis.

### D. Pelanggan mengecek status
1. Buka `/track` dari HP (tanpa login, tanpa registrasi).
2. Ketik nomor nota (`SRV-…`) **atau** nomor WhatsApp.
3. Lihat timeline pengerjaan, rincian biaya, dan sisa hari garansi.

---

## Algoritma Penting (Untuk Pertanyaan Asesor)

**1. Nomor servis sequential & anti race-condition**
`ServiceOrder::generateServiceCode()` membungkus pencarian nota terakhir dalam `DB::transaction()` + `lockForUpdate()`, sehingga dua kasir yang menyimpan bersamaan tidak pernah mendapat nomor ganda.

**2. Pengurangan & pengembalian stok transaksional**
`addPart()`: kunci baris part dengan `lockForUpdate()`, validasi `stock >= qty` (tolak dengan pesan yang menyebut sisa stok), kurangi stok, catat ke pivot `service_order_parts`, lalu `recalculateTotal()` — semuanya dalam satu transaksi; gagal di tengah = tidak ada perubahan. `removePart()` dan `cancel()` membalikkan stok dengan mekanisme yang sama dan melaporkan rinciannya di pesan.

**3. Perhitungan garansi**
Saat checkout: `warranty_expires_at = Carbon::today()->addDays(warranty_days)`. Di portal tracking, sisa hari dihitung dinamis (`diffInDays` terhadap tanggal hari ini) sehingga hitung mundur selalu akurat.

**4. Keamanan & validasi**
Semua form dilindungi CSRF token, password Bcrypt (`casts` model), middleware `role:admin` membatasi modul master data, dan seluruh input divalidasi server-side dengan pesan berbahasa Indonesia yang menjelaskan cara memperbaiki.

---

## Konfigurasi Kontak Bengkel

Alamat & nomor WhatsApp yang muncul di tanda terima, faktur, dan tombol WA portal dikendalikan lewat `.env` (lihat `config/workshop.php`):

```env
WORKSHOP_NAME="Tracket Bengkel Servis"
WORKSHOP_ADDRESS="Jl. Teratai No. 45"
WORKSHOP_WA_NUMBER=6281234567890      # format internasional tanpa +
WORKSHOP_WA_DISPLAY="0812-3456-7890"  # format tampil di dokumen
```

Ubah di `.env` lalu jalankan `php artisan config:clear` — ketiga dokumen ikut berubah.

---

## Menjalankan Test

```bash
php artisan test
# atau:
composer test
```

Test mencakup: root `/` redirect ke portal tracking, dan render halaman-halaman inti.

---

## Troubleshooting

| Gejala | Penyebab | Solusi |
|---|---|---|
| `could not find driver` | Ekstensi PDO database belum aktif | Aktifkan `extension=pdo_mysql` (atau `pdo_sqlite`) di `php.ini`, restart Apache |
| `Connection refused (SQLSTATE[HY000] [2002])` | MySQL belum jalan | XAMPP Control Panel → Start MySQL |
| `Base table or view not found` | Belum migrasi | `php artisan migrate:fresh --seed` |
| `No application encryption key` | `.env` tanpa APP_KEY | `php artisan key:generate` lalu `php artisan config:clear` |
| Halaman tampil tanpa styling / class tidak bekerja | Belum build Tailwind | `npm install && npm run build` |
| `419 Page Expired` saat submit form | Sesi/CSRF kadaluarsa | Refresh halaman, login ulang; jangan biarkan form terbuka berjam-jam |
| Gambar/font tidak muncul | `php artisan storage:link` belum dijalankan (untuk storage) | `php artisan storage:link` |
| Port 8000 terpakai | Aplikasi lain memakai port | `php artisan serve --port=8080` |
| Login gagal terus | Seeder belum jalan / DB salah koneksi | Pastikan `migrate:fresh --seed` sukses di koneksi yang benar |

---

## Mode Demo Asesor

- Chip **"Contoh Data Uji"** di portal `/track` hanya tampil ketika `APP_ENV=local` (default `.env` proyek ini). Selama demo, biarkan `local` agar asesor bisa 1-klik data contoh dari HP-nya.
- **Sebelum aplikasi dipakai bengkel sungguhan**, ganti di `.env`: `APP_ENV=production`, `APP_DEBUG=false`, lalu `php artisan config:clear` — chip demo hilang otomatis dan pesan error tidak lagi menampilkan stack trace.
- Skenario demo 3 menit tersedia di `PANDUAN_ASESMEN_BNSP.md`.

---

*Dibangun untuk Standar Kompetensi SKKNI BNSP Bidang Pemrograman Web. Lisensi kode aplikasi: mengikuti kebutuhan proyek; framework Laravel berlisensi MIT.*
