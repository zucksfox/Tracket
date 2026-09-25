# design.md — Design System: ServiceTrack & Warranty Hub
### (Revisi: gaya dashboard admin modern, referensi "Flup")

Menggantikan arah desain sebelumnya (blueprint biru-tinta). File ini jadi
sumber kebenaran tunggal untuk warna, font, dan layout — semua komponen
harus merujuk ke sini, bukan menebak sendiri.

## Konsep
Dashboard admin modern, bersih, terang — pola sidebar dua-lapis + card
metrik + chart, seperti dashboard SaaS pada umumnya. Prioritas: kejelasan
dan familiaritas visual (pengguna awam sudah sering lihat pola ini di
aplikasi lain), bukan orisinalitas ekstrem.

## Warna
| Token | Hex | Peran |
|---|---|---|
| `bg-app` | `#F3F4F5` | Latar belakang utama di luar card |
| `bg-sidebar` | `#FAFAFA` | Latar sidebar |
| `surface` | `#FFFFFF` | Card, panel, tooltip |
| `accent-primary` | `#1E7A5C` | Hijau emerald — logo, nav aktif, tombol utama |
| `accent-primary-soft` | `#E3F3EA` | Background nav item aktif |
| `trend-up` | `#2FA36B` | Indikator tren naik |
| `trend-down` | `#E2574C` | Indikator tren turun / stok kritis |
| `chart-blue` | `#6366F1` | Seri chart 1 (mis. "Biaya Jasa") |
| `chart-orange` | `#F5A742` | Seri chart 2 (mis. "Pendapatan Part") |
| `text-primary` | `#1F2937` | Heading, angka utama |
| `text-muted` | `#6B7280` | Label, teks sekunder |
| `border` | `#E5E7EB` | Garis pemisah, outline input |

Donut/breakdown chart boleh pakai palet multi-warna sekunder:
`#7C3AED` `#EC4899` `#14B8A6` `#22C55E` `#38BDF8` `#F97316` — dipakai
konsisten per kategori (kategori sparepart, kategori kerusakan, dll),
jangan acak tiap render.

## Tipografi
- **Semua teks UI**: Inter (fallback: Plus Jakarta Sans, DM Sans, system-ui).
- Angka metrik besar: font-weight 600–700, tabular numerals aktif
  (`font-variant-numeric: tabular-nums`) supaya kolom angka rapi sejajar.
- Label kecil di atas angka: 12–13px, `text-muted`, tanpa ALL-CAPS
  kecuali untuk header grup sidebar (mis. "OPERASIONAL").
- Kode servis (SRV-202609-0001): tetap pakai monospace (IBM Plex Mono
  atau JetBrains Mono) — satu-satunya pengecualian dari Inter, karena
  datanya memang kode/serial.

## Layout

### Struktur halaman (placement umum)
Mengikuti pola dashboard standar — jangan improvisasi struktur baru:
- **Sidebar**: fixed di kiri, tinggi penuh dari atas ke bawah layar.
- **Topbar**: fixed di atas, di sebelah kanan sidebar, tinggi ~64px.
  Isi dari kiri ke kanan: judul halaman (atau breadcrumb), lalu di
  kanan: kolom pencarian (opsional per halaman), ikon notifikasi,
  lalu tombol profil (inisial + nama + peran). Klik profil membuka menu
  akun ke bawah; logout hanya dari situ, setelah konfirmasi.
- **Area konten utama**: mengisi sisa ruang di kanan sidebar & di bawah
  topbar, dengan padding luar konsisten (~24–32px), scroll vertikal
  independen dari sidebar/topbar yang tetap diam.
- **Struktur dalam konten**: dari atas ke bawah — judul halaman/aksi
  utama (mis. tombol "+ Servis Baru" di kanan atas konten, sejajar
  judul) → baris card metrik (grid horizontal, wrap ke bawah di layar
  sempit) → chart/tabel utama di bawahnya (grid 2 kolom di desktop,
  1 kolom bertumpuk di mobile).
- Urutan ini konsisten di semua halaman modul (Servis, Sparepart,
  Laporan) — hanya isi kontennya yang berbeda, kerangkanya tetap sama
  supaya pengguna tidak perlu belajar ulang navigasi tiap pindah halaman.

### Sidebar
- Dua lapis: rail ikon sempit (~56px, selalu terlihat) + panel label
  (~220px, bisa di-collapse dengan tombol panah).
- Dikelompokkan per kategori sesuai modul aplikasi, bukan generik:
  - **OPERASIONAL**: Dashboard, Servis Masuk, Antrian Teknisi, Pelanggan
  - **INVENTORI**: Suku Cadang, Stok Kritis
  - **KEUANGAN**: Laporan, Riwayat Transaksi
  - **SISTEM**: Pengaturan, Manajemen Pengguna
- Nav item aktif: background `accent-primary-soft`, teks `accent-primary`,
  ikon terisi (bukan outline).
- Sidebar hanya navigasi. Identitas akun dan aksi logout ada di top bar:
  klik profil membuka menu kecil (nama, peran, Keluar akun) yang meluncur
  turun. Keluar akun selalu lewat dialog konfirmasi "Keluar akun?".

### Dashboard utama
- Baris card metrik di atas (4–5 card): ikon kecil + label + angka besar
  + indikator tren. Untuk konteks bengkel: **Servis Aktif**, **Pendapatan
  Bulan Ini**, **Servis Selesai**, **Sparepart Stok Kritis**.
- Chart batang: dua seri warna (`chart-blue` = Biaya Jasa, `chart-orange`
  = Pendapatan Sparepart) per rentang tanggal, dengan tooltip popup saat
  hover (card putih rounded, shadow lembut).
- Breakdown donut: kategori kerusakan servis terbanyak (bukan kategori
  produk seperti referensi asli).
- List ringkas: status garansi aktif per pelanggan atau teknisi paling
  banyak menangani servis bulan ini — gantikan "Sales by countries".

### Card & komponen umum
- Border-radius: 12–16px konsisten di semua card.
- Shadow: soft, menyebar tipis (`0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04)`),
  jangan shadow tajam/gelap.
- Padding internal card: minimal 20px, jangan mepet.
- Toggle switch untuk pengaturan on/off (mis. mode gelap, notifikasi),
  gaya pill sesuai referensi.

### Tabel/daftar servis (bukan di dashboard utama, tapi halaman list)
- Tetap tabel/list standar dengan baris bergantian warna latar sangat
  tipis untuk keterbacaan, badge status berbentuk pill kecil mengikuti
  warna `trend-up`/`trend-down`/`accent-primary` sesuai status.

## Motion
Animasi wajib memberi informasi tentang perubahan state, arah navigasi, atau hasil aksi. Jangan menambahkan animasi dekoratif tanpa tujuan.

### Durasi dan easing
- Micro-interaction: 150–250ms.
- Transisi state atau halaman: 300–400ms bila memang diperlukan.
- Elemen muncul memakai `ease-out`; elemen hilang memakai `ease-in`.
- Jangan gunakan animasi linear untuk interaksi UI.

### Interaksi dan feedback
- Tombol mengecil ringan sekitar `scale(.97)` saat ditekan, lalu kembali normal.
- Submit form berubah ke state loading dengan spinner kecil dan teks `Menyimpan...`; tidak boleh ada delay buatan yang menghalangi pekerjaan.
- Border input berubah halus saat focus.
- Field error memakai shake singkat sekitar 300ms, border merah, dan pesan error fade-in.

### State aplikasi
- Toast stok muncul dari kanan atas dengan slide/fade singkat sekitar 200ms dan hilang dengan fade-out, bukan menghilang mendadak.
- Badge notifikasi melakukan pulse/scale satu kali saat notifikasi baru masuk.
- Perubahan status servis memakai efek cap stempel: scale sedikit lebih besar lalu kembali normal sekitar 200ms.
- Loading data memakai skeleton shimmer ringan. Transisi skeleton ke data memakai fade singkat; jangan menampilkan spinner besar di tengah halaman.
- Perpindahan halaman/tab hanya mem-fade area konten utama sekitar 150ms. Sidebar dan topbar tetap statis; jangan gunakan slide antar halaman.

### Data visual
- Bar chart dan donut chart boleh animasi masuk satu kali saat halaman pertama dimuat, sekitar 400–500ms.
- Filter atau refresh data tidak boleh memicu animasi masuk ulang yang mengganggu pekerjaan operator.

### Batasan aksesibilitas dan performa
- Hormati `prefers-reduced-motion: reduce` dengan mematikan animasi non-esensial.
- Jangan menganimasikan semua section saat load, jangan memakai slide/scale besar saat hover, dan jangan memakai library animasi berat jika CSS cukup.

## Nada tulisan (microcopy)
- Bahasa Indonesia natural. Label singkat dan jelas: "Servis Aktif"
  bukan "Jumlah Total Servis yang Sedang Berjalan".
- Pesan error tetap spesifik dan actionable, contoh: "Stok tidak
  mencukupi, tersisa 1 pcs" — bukan pesan generik.
