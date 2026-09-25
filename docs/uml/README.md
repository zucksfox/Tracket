# UML — Tracket (Sistem Manajemen Bengkel Servis &amp; Garansi)

Sembilan diagram UML yang dibuat dari kode nyata di repositori ini. Setiap
diagram tersedia dalam tiga bentuk:

| Berkas | Kegunaan |
|---|---|
| `*.puml` | sumber PlantUML — dapat diedit dan di-render ulang |
| `*.svg` | vektor, tajam di segala ukuran — dipakai untuk lampiran atau slide |
| `*.png` | gambar raster, untuk ditempel ke dokumen Word/PDF |

## Daftar diagram

| # | Berkas | Jenis | Isi |
|---|---|---|---|
| 1 | `01-diagram-kelas-model` | Class | Model domain: 6 kelas Eloquent, relasi beserta aturan kunci asing, trait `LogsActivity`, relasi polymorphic, enum status |
| 2 | `02-diagram-kelas-ekspor-laporan` | Class | Lapisan ekspor: antarmuka `ExportableReport`, kelas dasar abstract, dua implementasi format, registry, arsip, pemetaan kolom |
| 3 | `03-diagram-kelas-lapisan-http` | Class | 10 controller, pewarisan dari `Controller`, middleware, ketergantungan ke lapisan domain |
| 4 | `04-diagram-urutan-check-in` | Sequence | Check-in servis: middleware peran → validasi → transaksi → pembuatan nomor nota dengan kunci baris |
| 5 | `05-diagram-urutan-alur-servis-checkout` | Sequence | Perpindahan status, pemasangan suku cadang, checkout dan pembayaran, pembatalan dengan pengembalian stok |
| 6 | `06-diagram-urutan-ekspor-laporan` | Sequence | Ekspor laporan ke berkas: registry → pembacaan bertahap → render → tulis disk → unduh ulang |
| 7 | `07-diagram-aktivitas-servis-garansi` | Activity | Perjalanan lengkap satu unit servis, dari pelanggan datang sampai masa garansi berakhir |
| 8 | `08-diagram-komponen-arsitektur` | Component | Susunan lapisan aplikasi: peramban, Laravel, MySQL, folder arsip, konsumen berkas |
| 9 | `09-diagram-kelas-oop` | Class | Ringkasan konsep berorientasi objek: pewarisan, antarmuka, trait, kelas abstract |

## Kaitan diagram dengan ketentuan ujian praktik

| Ketentuan | Diagram yang membuktikan |
|---|---|
| a. Program sesuai rancangan | 1, 8 — struktur nyata yang dibandingkan dengan `RANCANGAN.md` |
| c. Antarmuka input dan output | 4, 5, 6, 7 — alur yang dilihat pengguna |
| d. Percabangan dan pengulangan | 4, 5, 6, 7 — setiap `alt`, `loop`, dan percabangan pada aktivitas |
| e. Prosedur, fungsi, method | 1, 2, 3 — daftar method beserta nilai kembaliannya |
| f. Array dua dimensi | 2 — `ReportColumnMap` memasok header dan isi baris |
| g. Simpan dan baca media penyimpanan | 6 — tulis berkas ke disk lalu baca kembali |
| h. Akses, pewarisan, polymorphism, overloading, antarmuka | 1, 2, 3, 9 |
| i. Dua namespace atau lebih | 2, 3, 8 — batas paket sesuai namespace |
| j. Pustaka eksternal | 8 — Laravel, PHPUnit, MySQL, pengolah angka |
| k. Basis data | 1, 6 — kelas model, kunci asing, pembacaan bertahap |
| l. Dokumentasi | seluruh diagram, khususnya 8 sebagai peta keseluruhan |

## Cara membuka

* Berkas `.svg` dan `.png` dapat dibuka langsung dengan peramban atau penampil
  gambar bawaan Windows.
* Berkas `.puml` dapat diedit di VS Code dengan ekstensi PlantUML, atau
  ditempel ke <https://www.plantuml.com/plantuml>.

## Cara membuat ulang setelah kode berubah

Diagram ini menggambarkan keadaan kode pada commit `6323cd2`. Bila kelas,
method, atau relasinya berubah, perbarui berkas `.puml` yang bersangkutan lalu
render ulang. Tanpa memasang apa pun, cukup kirim berkasnya ke layanan render:

```bash
curl -s -o 01-diagram-kelas-model.png \
  -X POST https://kroki.io/plantuml/png \
  -H "Content-Type: text/plain" \
  --data-binary @01-diagram-kelas-model.puml

# ganti /png menjadi /svg untuk memperoleh versi vektor
```

Bila Java tersedia, render lokal lebih cepat dan tanpa jaringan:

```bash
java -jar plantuml.jar -tpng *.puml
java -jar plantuml.jar -tsvg *.puml
```

### Catatan saat mengedit

* `trait` tidak dikenali bila ditulis di dalam blok `package` — tulis sebagai
  `class NamaTrait <<trait>>` agar tetap ter-render.
* PlantUML memotong gambar pada 4096 piksel. Bila diagram melebar melewati
  batas itu, kecilkan `nodesep`, `ranksep`, dan `dpi`, atau tambahkan
  `hide members` supaya hanya struktur yang tampil.
* Hindari kata `overload` dan `use` pada nama kelas atau alias — keduanya
  bertabrakan dengan kata kunci PlantUML dan menyebabkan galat sintaks.
