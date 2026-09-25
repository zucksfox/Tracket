# PRODUCT REQUIREMENT DOCUMENT (PRD)

**Nama Produk:** ServiceTrack & Warranty Hub
**Versi Dokumen:** 1.2 FINAL (konsolidasi seluruh keputusan — siap eksekusi)
**Target Pelaksanaan Ujian:** HARI INI (H-3 jam)
**Lokasi Direktori:** D:\project\project_bnsp

> **BACA DULU SEBELUM CODING:** Bagian 0 di bawah ini prioritas wajib
> vs opsional. Kerjakan urutan WAJIB dulu sampai selesai. Kalau waktu
> masih sisa, baru kerjakan OPSIONAL. Jangan mulai dari opsional.

---

## 0. PRIORITAS EKSEKUSI (WAJIB DIIKUTI KARENA WAKTU TERBATAS)

### WAJIB (inti kompetensi, harus selesai)
1. Setup Laravel + migration 5 tabel + seeder data dummy.
2. Auth + role middleware (Admin vs Kasir).
3. Master data (Customers, Spareparts) + validasi server-side dasar.
4. Check-in servis + auto-generate kode servis.
5. Repair flow: ubah status + pakai sparepart + potong/rollback stok.
6. Checkout: input metode bayar (Tunai ATAU QR — Tunai jadi fallback
   wajib berfungsi), aktivasi garansi, cetak faktur.
7. Public tracking portal (`/track`) dasar: input nomor servis/No. HP,
   tampilkan status + garansi.

### OPSIONAL (kerjakan HANYA kalau poin wajib sudah selesai)
- Notifikasi real-time stok kritis (fallback: badge stok kritis biasa
  di halaman sparepart, tanpa real-time, tetap valid untuk demo).
- Generate QR code sungguhan (fallback: tombol "Tandai Sudah Dibayar"
  manual tanpa QR visual — bisnis logic tetap sama, cuma tanpa gambar QR).
- Animasi/motion detail (fallback: transisi CSS bawaan Bootstrap/Tailwind,
  tidak perlu custom).
- Logo di semua titik (fallback: logo cukup di sidebar + halaman login).
- Full redesign dashboard sesuai referensi Flup (fallback: styling dasar
  ikuti token warna & font di `design.md`, tanpa perlu chart kompleks).

---

## 1. Latar Belakang & Tujuan

Banyak bengkel reparasi (smartphone, laptop, motor, elektronik) skala UMKM
masih mengelola nota servis secara manual di kertas atau spreadsheet.
Hal ini memicu 3 masalah utama:
- Pelanggan terus-menerus menghubungi admin/teknisi hanya untuk menanyakan
  status pengerjaan.
- Pelanggan kehilangan nota fisik sehingga memicu sengketa klaim garansi.
- Suku cadang (sparepart) sering selisih/bocor karena pencatatan
  keluar-masuk barang tidak terhubung langsung dengan nota servis.

**Tujuan Produk:** platform manajemen bengkel yang mencatat siklus
lengkap penerimaan perangkat, pemotongan stok sparepart otomatis,
penghitungan masa aktif garansi, pembayaran sebelum unit diambil,
halaman pelacak publik tanpa login, serta cetak bukti terima dan
invoice garansi.

---

## 2. Pemetaan Standar Kompetensi BNSP (SKKNI)

- **J.620100.004.01** — struktur data & basis data relasional (5 tabel,
  relasi 1:N & N:M).
- **J.620100.017.02** — algoritma pemrograman (kalkulasi harga, potong
  stok, expiry garansi, validasi pembayaran).
- **J.620100.023.02** — dokumen & antarmuka pengguna (UI responsive,
  validasi form).
- **J.620100.025.02** — pengujian software (validasi server-side & error
  handling).
- **J.620100.009.01** — library pihak ketiga (Blade/Bootstrap, print
  engine, QR generator).

---

## 3. User Roles & Otorisasi

**A. Admin (merangkap Teknisi)**
- Kelola master data (pelanggan, sparepart, akun pengguna).
- Melihat laporan pendapatan & laba bersih.
- Mengerjakan servis secara teknis: ubah status (Diagnosa → Pengerjaan
  → Selesai Pengerjaan), pilih & pakai sparepart (stok otomatis
  terpotong), tambah catatan teknis kerusakan.
- Akses penuh ke seluruh sistem.

**B. Kasir**
- Registrasi unit servis baru (check-in) & cetak Tanda Terima.
- Memproses checkout: konfirmasi metode pembayaran (Tunai/QR), input
  masa garansi, cetak Faktur & Kartu Garansi.
- **Tidak bisa**: ubah status pengerjaan, pilih/pakai sparepart, akses
  laporan laba bersih atau hapus master data.

**C. Pelanggan (Akses Publik / Tanpa Login)**
- Akses `/track`, input nomor servis atau No. HP, lihat progres &
  status garansi.

---

## 4. Skop Fitur & Detil Spesifikasi

### Modul 1: Autentikasi & Keamanan
- Login email/username + password hashing (Bcrypt).
- Role middleware: Kasir diblokir dari Repair Flow & Laporan Keuangan;
  Admin akses penuh.
- Proteksi CSRF di semua form.

### Modul 2: Master Data Management
- Pelanggan: Nama, No. WhatsApp (unik), Alamat.
- Sparepart: Kode Part (unik), Nama, Kategori, Stok, Harga Modal, Harga
  Jual.
- Badge stok kritis (kuning/merah jika stok <= 2 pcs).
- Validasi server-side: field wajib, numerik untuk stok/harga/No. HP,
  tidak boleh negatif.

### Modul 3: Penerimaan Servis (Check-In) — dikerjakan Kasir
- Form: pelanggan (search by No. WhatsApp, auto-fill jika sudah ada),
  tipe/seri perangkat (wajib), IMEI (opsional), keluhan (wajib),
  kelengkapan (opsional), estimasi biaya (default 0).
- Auto-generate kode servis `SRV-YYYYMM-XXXX` (dibungkus DB transaction
  untuk cegah duplikasi).
- Cetak Surat Tanda Terima (format A4).

### Modul 4: Repair Flow — dikerjakan Admin
- Siklus status: Antrian → Diagnosa → Pengerjaan → Selesai Pengerjaan →
  Diambil/Selesai / Batal.
- Perubahan status via 1 tombol aksi.
- Tambah sparepart: validasi stok cukup → potong otomatis → simpan ke
  `service_order_parts`. Jika dibatalkan, stok di-rollback.
- Total tagihan (jasa + sparepart) dihitung otomatis.

### Modul 5: Checkout, Pembayaran & Garansi — dikerjakan Kasir
- Setelah status "Selesai Pengerjaan", kasir memproses checkout:
  - Pilih metode bayar: **Tunai** (langsung tandai lunas) atau **QR**
    (tampilkan QR, lalu kasir konfirmasi manual setelah menerima
    notifikasi transfer).
  - Status servis TIDAK bisa berubah jadi "Diambil" sebelum
    `payment_status = 'paid'`.
  - Input masa garansi (preset 30/60/90 hari).
  - Sistem hitung otomatis `warranty_expires_at = tanggal ambil + warranty_days`.
  - Cetak Faktur (mencantumkan status LUNAS + waktu bayar) & Kartu
    Garansi.

### Modul 6: Public Tracking Portal
- URL `/track`. Satu kolom input (nomor servis ATAU No. HP, sistem
  deteksi otomatis).
- Tampilkan: timeline status, catatan teknisi, badge garansi
  aktif/habis.
- Pesan error ramah jika data tidak ditemukan.
- Wajib responsive di layar HP.

### Modul 7: Laporan & Cetak — akses Admin saja
- Cetak: Tanda Terima, Faktur, Kartu Garansi (satu format A4).
- Laporan: filter tanggal & status, ringkasan pendapatan jasa +
  sparepart.

---

## 5. Struktur Basis Data

**Tabel 1: users**
- id (PK), name, email (unique), password, `role` (Enum: **'admin', 'cashier'**), timestamps

**Tabel 2: customers**
- id (PK), name, phone (unique, index), address (nullable), timestamps

**Tabel 3: spareparts**
- id (PK), part_code (unique), name, category, stock (default 0), buy_price, sell_price, timestamps

**Tabel 4: service_orders**
- id (PK), service_code (unique, index), customer_id (FK), technician_id (FK -> users.id, nullable — praktiknya selalu akun Admin), device_name, device_serial (nullable), issue_description, accessories_included (nullable), status (Enum: pending, diagnosing, in_progress, ready, completed, cancelled), labor_cost, total_cost, **payment_status (Enum: 'unpaid', 'paid', default 'unpaid')**, **payment_method (Enum: 'cash', 'qr', nullable)**, **paid_at (timestamp, nullable)**, warranty_days, warranty_expires_at, technician_notes, timestamps

**Tabel 5: service_order_parts**
- id (PK), service_order_id (FK, cascade delete), sparepart_id (FK), quantity, unit_price, subtotal, timestamps

---

## 6. Aturan Bisnis Khusus

1. **Kode Servis**: `SRV-[TAHUN][BULAN]-[URUTAN_4_DIGIT]`, query max id
   bulan berjalan dibungkus transaction/lock.
2. **Potong Stok**: jika `stock >= qty` → potong & simpan; else tolak
   dengan pesan "Stok suku cadang tidak mencukupi".
3. **Rollback Stok**: servis dibatalkan/part dihapus → stok dikembalikan.
4. **Validasi Pembayaran**: status hanya bisa jadi `completed` jika
   `payment_status = 'paid'`. Jika kasir coba ubah status sebelum
   lunas → tolak dengan pesan "Pembayaran belum dikonfirmasi".
5. **Masa Garansi**: saat `completed` → `warranty_expires_at = CURDATE() + INTERVAL warranty_days DAY`.
   Tracking hitung sisa hari: `DATEDIFF(warranty_expires_at, CURDATE())`.

---

## 7. Rencana Implementasi Teknis

- **Framework**: Laravel 10/11 (PHP 8.2, XAMPP).
- **Styling**: mengikuti `design.md` (token warna, tipografi, layout)
  — untuk hari ini cukup terapkan token warna & struktur dasar, styling
  detail (animasi, logo penuh) masuk kategori opsional.
- **Database**: MySQL (XAMPP localhost).
- **Seed Data**: 5 servis berbagai status, 10 sparepart, 5 pelanggan,
  1 akun admin + 1 akun kasir — siap demo tanpa input manual dari nol.

---

## 8. Urutan Eksekusi Sisa Waktu

Ikuti persis daftar WAJIB di Bagian 0. Kalau di tengah jalan waktu makin
mepet, potong dari fitur OPSIONAL dulu — jangan potong dari daftar
WAJIB. Kalau modul 6 (tracking portal) belum sempat dipercantik,
versi paling sederhana (tampilkan data mentah tanpa styling advance)
tetap lebih baik daripada modul itu tidak ada sama sekali.
