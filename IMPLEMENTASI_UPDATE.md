# Verifikasi implementasi update.md

## Hasil eksekusi

- `php artisan test --compact`: **120 passed (436 assertions)**, eksekusi terakhir 15.74 detik.
- `npm run build`: berhasil; Vite 7.3.6, 58 modul, selesai 712 ms.
- `php artisan view:clear`: berhasil.
- `git diff --check`: exit 0 (peringatan normalisasi CRLF, tanpa kesalahan whitespace).
- `php artisan migrate --force`: migrasi cashier/payment berhasil sebagai batch 3.
- `php artisan migrate:status`: semua migrasi berstatus Ran.
- `python storage/qa_http.py`: **40 pemeriksaan HTTP PASS**, termasuk CRUD check-in, transisi Admin, larangan Kasir, cash checkout, QR tanpa/dengan konfirmasi, invoice dan tracking.
- `python storage/qa_browser.py`: Chrome headless PASS, tanpa JS pageerror; dashboard admin/kasir, menu akun/dialog batal logout, guard form repair, invoice PDF A4, tracking mobile lebar 390 tanpa overflow horizontal.

## Wajib

| Kebutuhan | Bukti / implementasi |
|---|---|
| Laravel + 5 tabel inti + dummy | Laravel 12 dipertahankan; database QA kosong dimigrasi dan diseed menghasilkan 2 pengguna (admin/kasir), 5 pelanggan, 10 part, 5 servis / 5 status berbeda |
| Auth dan role Admin/Kasir | Route check-in/lookup untuk admin+cashier, repair admin+technician lama, master/laporan admin; pengujian direct POST dan UI |
| Master + validasi | CRUD tetap berjalan; nomor HP 08 numerik, unique, stok/harga nonnegatif; cashier tidak boleh mutasi master |
| Check-in + kode otomatis | HTTP Kasir membuat servis nyata pada DB QA; tanda terima 200; kode berformat SRV-YYYYMM-XXXX |
| Repair + stok | Transisi berurutan/same-state notes, lock order sebelum mutasi; regresi stok, cancellation dua kali, wrong-parent part, terminal immutability |
| Checkout + garansi + faktur | Tunai dan QR manual atomik; payment paid + timestamp + completed + garansi; QR belum dikonfirmasi ditolak; checkout ulang ditolak; LUNAS/waktu bayar dicetak |
| Tracking publik | Tanpa login, kode/HP lengkap, status/catatan/garansi; HTTP dan browser mobile |

## Integrasi database operasional

Database aktual: MySQL driver → MariaDB **10.1.29**, database **servicetrack**. Laravel introspeksi awal gagal karena server lama tidak memiliki generation_expression. Migrasi menggunakan query metadata terbatas dan ALTER role kompatibel; SQLite tetap memakai change() untuk menghapus enum CHECK lama.

Backup sebelum perubahan:
`storage/app/private/backups/servicetrack-before-payment-20260925-053214.sql`

Ukuran 16949 byte; SHA-256 `3a85f99f8c6c3d24a603283da69ed6e30d059787935e231fc530190ed869b96f`.

Sebelum/sesudah: servis **6 → 6**, pengguna **2 → 3** (hanya tambah Kasir). Kredensial/nama/peran dua akun lama dibandingkan dan identik. Servis lama yang diberi label paid otomatis: **0**. Tidak melakukan reset/reseed database operasional. Seed lengkap hanya pada SQLite terpisah `storage/app/private/qa-payment.sqlite`.

## URL dan evidence

- Operasional: `http://127.0.0.1:8000/login`, `/track`, `/dashboard`, `/service/check-in` — smoke read-only HTTP 200 (quick-login kasir dipakai untuk sesi).
- QA terisolasi saat verifikasi: `http://127.0.0.1:8765` — transaksi uji hanya SQLite, bukan operasional.
- `storage/app/private/qa-http-results.json`: hasil 40 pemeriksaan.
- `storage/app/private/qa-evidence/cashier-dashboard.png`
- `storage/app/private/qa-evidence/admin-dashboard.png`
- `storage/app/private/qa-evidence/paid-invoice.png`
- `storage/app/private/qa-evidence/tracking-mobile.png`
- `storage/app/private/qa-evidence/paid-invoice-a4.pdf` (55851 byte).

## Batasan / opsional

- QR visual/gateway tidak dibuat; fallback konfirmasi manual sesuai prioritas PRD, harus cek transfer merchant sungguhan.
- Stok kritis badge/filter dan mekanisme notifikasi yang sudah ada dipertahankan; sidebar hanya satu Suku Cadang.
- Laporan menyediakan filter tanggal/status dan ringkasan pendapatan jasa/part. **Laba bersih akuntansi belum tersedia**: tidak ada biaya operasional maupun snapshot harga modal historis. Tidak mengklaim pendapatan sebagai laba. Data selesai lama tetap disertakan sebagai nilai servis, bukan bukti pelunasan.
- MariaDB 10.1 sangat lama; migrasi ini sudah teruji pada server tersebut, tetapi upgrade server direkomendasikan untuk deployment Laravel 12.
- Quick-login tetap fasilitas demo yang melewati password; wajib dinonaktifkan sebelum internet/public deployment.
- Browser bundled Playwright tidak tersedia: berhasil memakai Chrome terpasang. networkidle tidak cocok dengan aktivitas halaman; memakai DOMContentLoaded + assertion DOM. Screenshot/PDF benar-benar dibuat; tool analisis gambar terpisah timeout, jadi tidak mengklaim review visual oleh model.
- Tidak melakukan uji beban multi-kasir/concurrency stress atau pembayaran bank sungguhan.
