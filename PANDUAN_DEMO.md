# Panduan demo Tracket

## Akun dan akses

- Admin: `admin@tracket.test`, kata sandi awal `password`. Mengerjakan servis, mengelola pelanggan/suku cadang/pengguna, laporan, serta checkout.
- Kasir: `kasir@tracket.test`, kata sandi awal `password`. Check-in, pencarian pelanggan lewat nomor WhatsApp lengkap, pembayaran, garansi, tanda terima dan faktur.
- Akun teknisi lama tidak dihapus atau diubah. Teknisi tetap dapat mengerjakan servis tetapi tidak checkout atau mengakses laporan/master data.
- Seeder memakai firstOrCreate: kata sandi akun yang sudah ada tidak direset. Akun demo baru hanya admin dan kasir.
- Quick-login adalah fasilitas demo lokal yang melewati kata sandi. Nonaktifkan rute tersebut sebelum membuka akses internet.

## Alur demo

1. Login sebagai Kasir. Pilih **Servis Baru**. Masukkan nomor HP lokal `08…` (10–13 digit), pelanggan/perangkat dan keluhan. Pelanggan lama dicari dengan nomor lengkap, bukan potongan angka.
2. Simpan, lalu cetak **Tanda Terima**. Nomor servis dibuat otomatis.
3. Login sebagai Admin, buka servis: **Mulai Diagnosa → Mulai Pengerjaan → Tandai Siap Diambil**. Simpan catatan/tarif jasa dan pasang suku cadang. Stok terpotong; melepas part atau membatalkan servis mengembalikannya hanya sekali.
4. Login Kasir, buka servis siap diambil. Pilih **Tunai**, pastikan uang diterima, pilih garansi 30/60/90 hari (atau durasi khusus), lalu **Konfirmasi Lunas & Serahkan Unit**.
5. Alternatif **QR — konfirmasi manual**: gunakan QR merchant bengkel di luar aplikasi. Periksa uang benar-benar masuk lalu centang **Tandai Sudah Dibayar**. Tidak ada gambar QR palsu, integrasi bank atau konfirmasi otomatis. Tanpa centang, servis tetap siap diambil dan belum dibayar.
6. Cetak **Faktur & Garansi**: memuat LUNAS, metode dan waktu bayar. Masa garansi dihitung dari tanggal checkout. Servis selesai/batal tidak bisa diedit lagi.
7. Buka `/track` tanpa login; cari nomor servis atau nomor HP lengkap. Coba dari layar ponsel.
8. Admin membuka **Laporan**, memilih tanggal dan status. Ringkasan pendapatan hanya menghitung servis selesai, meskipun daftar dapat difilter ke status lain. Angka ini bukan laba bersih: biaya operasional dan harga modal historis belum dicatat.

## Database dan upgrade aman

Gunakan `php artisan migrate`, bukan `migrate:fresh`, untuk instalasi yang sudah berisi data. Backup database aktual sebelum upgrade. Migrasi tambahan mempertahankan akun/servis lama dan menambah `payment_status`, `payment_method`, `paid_at`. Servis selesai lama **tidak** otomatis dianggap lunas; faktur menampilkan pembayaran belum tercatat.

`php artisan db:seed` aman untuk pengulangan: tidak mengganti stok/kredensial/nama lama dan tidak menambah transaksi demo apabila tabel servis sudah berisi transaksi. Pada database baru hasilnya 5 pelanggan, 10 suku cadang, 5 servis berstatus berbeda, admin dan kasir. Gunakan database terpisah bila ingin mendemokan semua data tersebut tanpa mengubah operasional.

## Verifikasi

```bash
php artisan test
npm run build
php artisan view:clear
```

Setelah pembaruan UI, muat ulang browser dengan Ctrl+F5. Buka `/login`, `/dashboard`, `/service/check-in`, `/services`, `/reports` (admin), dan `/track`.
