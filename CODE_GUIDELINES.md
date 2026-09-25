# Pedoman Penulisan Kode — Tracket

Dokumen ini adalah pedoman resmi penulisan kode untuk proyek Tracket. Isinya
bukan sekadar daftar keinginan: seluruh aturan di sini **ditegakkan mesin** dan
dapat diperiksa ulang oleh asesor dengan satu perintah.

| Alat | Berkas konfigurasi | Fungsi |
|---|---|---|
| Laravel Pint 1.30.4 | `pint.json` | Memeriksa dan memperbaiki gaya kode PHP (PSR-12 + gaya Laravel) |
| EditorConfig | `.editorconfig` | Menyeragamkan indentasi, akhir baris, dan pengodean berkas |
| PHPUnit 11.5 | `phpunit.xml` | Menjalankan pengujian |
| GitHub Actions | `.github/workflows/pint.yml` | Memeriksa gaya kode otomatis setiap kali ada push |

Perintah pemeriksaan (tidak mengubah berkas):

```bash
php vendor/bin/pint --test      # keluaran "passed" berarti seluruh berkas patuh
```

Perintah perbaikan otomatis:

```bash
php vendor/bin/pint             # memperbaiki berkas yang melanggar
php vendor/bin/pint app/Models  # hanya folder tertentu
```

---

## 1. Standar acuan

| Acuan | Cakupan |
|---|---|
| **PSR-12** (*Extended Coding Style*) | Standar resmi PHP-FIG untuk penulisan kode PHP |
| **PSR-4** (*Autoloading Standard*) | Pemetaan namespace ke folder, diatur di `composer.json` |
| **PSR-1** (*Basic Coding Standard*) | Aturan dasar: satu kelas satu berkas, penamaan |
| **Preset `laravel`** | Kumpulan aturan Pint yang dibangun di atas PSR-12 |
| **PSR-3** | Antarmuka pencatat log (dipakai Laravel secara internal) |
| **PSR-7 / PSR-15 / PSR-11** | HTTP message, middleware, container (dipakai kerangka kerja) |

`pint.json` pada proyek ini:

```json
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": false,
        "final_class": false,
        "phpdoc_align": { "align": "left" }
    },
    "exclude": ["bootstrap/cache", "node_modules", "public/build", "storage", "vendor"]
}
```

Preset `laravel` dipilih (bukan `psr12` murni) karena proyek memakai Laravel:
preset ini adalah PSR-12 ditambah konvensi resmi Laravel, sehingga gaya kode
sejalan dengan kerangka kerja yang dipakai. Tiga aturan ditimpa secara sadar —
dua dimatikan karena akan memaksa perubahan yang tidak perlu pada kode yang
sudah berjalan, satu disetel agar PHPDoc rata kiri supaya blok tag lebih mudah
dibaca saat berdampingan dengan komentar bahasa Indonesia.

---

## 2. Aturan yang ditegakkan mesin

Berikut aturan yang **benar-benar aktif** di proyek ini. Daftar ini diambil
dari laporan nyata saat pemeriksaan pertama dijalankan — setiap butir di bawah
pernah menegur kode di repositori ini sebelum diperbaiki:

| Aturan Pint | Yang dijaga | Contoh pelanggaran nyata di proyek ini |
|---|---|---|
| `single_quote` | Tanda kutip tunggal untuk string tanpa variabel | `"Akses Ditolak"` → `'Akses Ditolak'` |
| `concat_space` | Jarak di sekitar titik penyambung string | `'Halo '.$nama` → `'Halo ' . $nama` |
| `ordered_imports` | Urutan blok `use` menurut abjad | blok `use` dirapikan urutannya |
| `no_unused_imports` | Membuang `use` yang tidak dipakai | impor `BelongsTo` yang menganggur dibuang |
| `fully_qualified_strict_types` | Mengimpor kelas yang dipanggil dengan nama lengkap | `\App\Models\ActivityLog::create()` → `use` + nama pendek |
| `not_operator_with_successor_space` | Spasi setelah `!` | `!$request->user()` → `! $request->user()` |
| `binary_operator_spaces` | Jarak di sekitar operator biner | `$a=1` → `$a = 1` |
| `braces_position` | Letak kurung kurawal kelas dan method | kurawal naik ke baris deklarasi |
| `class_definition` | Bentuk penulisan deklarasi kelas | `class X {` pada satu baris dirapikan |
| `class_attributes_separation` | Satu baris kosong antar anggota kelas | antar properti dan method diberi jarak |
| `statement_indentation` | Indentasi pernyataan bertingkat | isi blok dirapikan 4 spasi |
| `method_chaining_indentation` | Indentasi pemanggilan berantai | `->where()->orderBy()` dirapikan menjorok |
| `blank_line_before_statement` | Baris kosong sebelum `return`, `throw`, `if` | `return` diberi baris kosong sebelumnya |
| `blank_line_after_namespace` | Baris kosong setelah `namespace` | ditambahkan |
| `trailing_comma_in_multiline` | Koma akhir pada larik/multibaris | ditambahkan agar diff lebih bersih |
| `single_line_empty_body` | Badan kosong ditulis `{}` | `function f() {}` |
| `unary_operator_spaces` | Jarak operator uner | `! $x`, `-1` |
| `no_whitespace_in_blank_line` | Tidak ada spasi pada baris kosong | dibersihkan |
| `single_blank_line_at_eof` | Tepat satu baris kosong di akhir berkas | ditambahkan |
| `line_ending` | Akhir baris seragam (LF) | CRLF dari Windows diseragamkan |
| `single_import_per_statement` | Satu `use` per baris | `use A, B;` dipecah |
| `phpdoc_align` | Perataan blok PHPDoc | disetel rata kiri lewat `pint.json` |

Hasil pemeriksaan terakhir pada 77 berkas PHP (`app/` 31, `tests/` 21,
`database/` 12, `config/` 11, `routes/` 2):

```
$ php vendor/bin/pint --test
{"tool":"pint","result":"passed"}
```

---

## 3. Aturan yang tidak bisa ditegakkan mesin

Pint hanya menjaga bentuk tulisan. Butir di bawah ini adalah kesepakatan yang
harus dipatuhi manusia, karena tidak ada alat yang bisa memeriksanya.

### 3.1 Penamaan

| Jenis | Bentuk | Contoh di proyek ini |
|---|---|---|
| Kelas | `PascalCase` | `ServiceOrderController`, `CsvReportExporter` |
| Method | `camelCase`, kata kerja | `generateServiceCode()`, `recalculateTotal()` |
| Variabel | `camelCase` | `$serviceOrder`, `$laborTotal` |
| Properti | `camelCase` | `$fillable`, `$disk` |
| Konstanta | `SCREAMING_SNAKE_CASE` | `CHUNK_SIZE`, `DIRECTORY`, `STATUSES` |
| Tabel basis data | `snake_case` jamak | `service_orders`, `service_order_parts` |
| Kolom basis data | `snake_case` | `warranty_expires_at`, `labor_cost` |
| Rute bernama | `titik` sebagai pemisah | `services.print-invoice`, `reports.export` |
| Berkas tampilan | `snake_case` | `print_receipt.blade.php`, `show.blade.php` |

### 3.2 Deklarasi tipe wajib

Setiap parameter dan nilai kembali diberi tipe. Ini bukan pilihan gaya, tetapi
bagian dari PSR-12 yang diperkuat praktik PHP modern:

```php
public function generateServiceCode(): string
public function recalculateTotal(): void
public function getWarrantyInfoAttribute(): array
private function rowFor(ServiceOrder $order, int $number): array
```

Tipe gabungan dan nullable ditulis eksplisit:

```php
public function getNextActionAttribute(): ?array
public function make(string $format, ?ExportableReport $fallback = null): ExportableReport
protected function cell(string|int|float|null $value): string
```

### 3.3 Visibilitas properti

Seluruh properti diberi visibilitas eksplisit. Properti Laravel yang memang
ditujukan untuk diakses kerangka kerja memakai `protected`; yang hanya dipakai
di dalam kelas sendiri memakai `private`:

```php
protected $fillable = [...];      // boleh diisi massal
protected $hidden = [...];        // disembunyikan dari keluaran array/JSON
private const CHUNK_SIZE = 200;   // hanya kelas ini
private readonly Filesystem $disk; // disuntikkan sekali, tidak boleh diubah
```

### 3.4 Bahasa komentar dan pesan

* Komentar menjelaskan **alasan**, bukan mengulang apa yang sudah terbaca dari
  kode. Contoh yang benar:

  ```php
  // Abaikan pembaruan yang hanya menyentuh kolom waktu.
  unset($changed['updated_at']);
  ```

  Contoh yang tidak diterima: `// menaikkan counter` di atas `$i++`.

* Komentar teknis ditulis dalam bahasa Indonesia agar sejalan dengan bahasa
  dokumen proyek, kecuali istilah yang memang tidak diterjemahkan
  (*middleware*, *chunk*, *soft delete*).

* Pesan galat yang dilihat pengguna selalu berbahasa Indonesia dan menjelaskan
  langkah perbaikan, bukan sekadar menyatakan kesalahan:

  ```php
  'Format nomor HP tidak dikenal. Gunakan awalan 08 tanpa tanda hubung, contoh: 081234567890.'
  ```

* Pesan untuk pengembang (pengecualian, galat internal) boleh berbahasa Inggris
  bila akan muncul di jejak galat.

### 3.5 PHPDoc

Setiap kelas, method publik, dan method `protected` diberi PHPDoc. Bentuknya
mengikuti pedoman PHP:

```php
/**
 * Susun tabel laporan dari query yang diberikan.
 *
 * Query pemanggil wajib sudah memuat relasi customer dan technician serta
 * agregat orderParts (withSum) dan sudah memiliki urutan (orderBy) yang pasti.
 *
 * @param  Builder<ServiceOrder>  $query
 * @return array{0: array<int, string>, 1: array<int, array<int, string|int|float|null>>}
 *
 * @throws RuntimeException Bila jumlah baris melampaui batas aman.
 */
public function build(Builder $query): array
```

Aturan ringkas:

1. Baris pertama: ringkasan satu baris, diakhiri titik.
2. Baris kosong, lalu keterangan tambahan bila perlu (prasyarat, perilaku
   khusus, alasan keputusan).
3. Tag `@param`, `@return`, `@throws` — hanya yang relevan.
4. Tipe generik Laravel ditulis lengkap (`HasMany<ServiceOrderPart, $this>`).
5. Method yang sudah jelas dari namanya dan bertipe sederhana tetap diberi
   ringkasan satu baris; tidak perlu mengulang tipe yang sudah ada di
   deklarasi method.

### 3.6 Struktur berkas dan namespace

* Satu kelas satu berkas; nama berkas sama persis dengan nama kelas.
* Namespace mengikuti folder (PSR-4), dipetakan di `composer.json`:
  `App\` → `app/`, `Tests\` → `tests/`, `Database\` → `database/`.
* Isi kelas diurutkan: properti → konstruktor → method publik → method
  `protected` → method `private`.
* Berkas PHP tidak diakhiri `?>`.

### 3.7 Larangan

| Dilarang | Alasan | Pengganti |
|---|---|---|
| `var_dump()`, `print_r()`, `dd()`, `dump()` | Tertinggal di kode produksi dan membocorkan data ke layar | Pengujian atau `php artisan tinker` |
| Kueri mentah dengan nilai yang ditempel langsung | Rawan SQL injection | Query builder / Eloquent dengan binding |
| Logika bisnis di dalam Blade | Sulit diuji | Pindahkan ke model, action, atau service |
| `env()` di luar folder `config/` | Nilai `env()` kosong setelah `config:cache` | `config('nama.kunci')` |
| Menulis ulang nilai mata uang dengan `float` murni di keluaran | Galat pembulatan | Cast `decimal:2` + `number_format()` |
| Menghapus data dengan `delete()` pada model ber-soft delete tanpa alasan | Menyembunyikan riwayat | `forceDelete()` bila memang disengaja |
| Menjalankan `migrate:fresh` pada basis data berisi data | Menghapus data operasional | `php artisan migrate` |

### 3.8 Pengujian

* Setiap perilaku baru disertai tes. Nama method tes memakai awalan `test_`
  dan menuliskan perilaku, bukan nama method yang diuji:

  ```php
  public function test_technician_cannot_export_or_download_reports(): void
  public function test_csv_render_writes_header_and_all_rows_with_semicolon_delimiter(): void
  ```

* Berkas tes dipisah menurut jenis: `tests/Unit` untuk logika yang berdiri
  sendiri, `tests/Feature` untuk alur lewat HTTP.
* Setiap fitur baru minimal menguji satu jalur berhasil dan satu jalur ditolak.
* Seluruh tes wajib lulus sebelum kode di-commit:

  ```bash
  php artisan test
  ```

---

## 4. Alur kerja sebelum commit

```bash
# 1. perbaiki gaya kode
php vendor/bin/pint

# 2. pastikan tidak ada pelanggaran tersisa
php vendor/bin/pint --test

# 3. pastikan seluruh tes lulus
php artisan test

# 4. bersihkan cache tampilan bila blade diubah
php artisan view:clear
```

Tiga langkah pertama juga dijalankan otomatis oleh GitHub Actions pada setiap
push (`.github/workflows/pint.yml`), sehingga kode yang melanggar standar akan
tertangkap tanpa bergantung pada disiplin manusia.

---

## 5. Bukti pemenuhan

| Butir ketentuan | Berkas bukti | Cara asesor memeriksa |
|---|---|---|
| Pedoman ditulis | `CODE_GUIDELINES.md` (dokumen ini) | dibaca |
| Pedoman dapat ditegakkan | `pint.json`, `.editorconfig` | `php vendor/bin/pint --test` |
| Pedoman dijalankan | 77 berkas PHP patuh | keluaran `{"result":"passed"}` |
| Pedoman berjalan otomatis | `.github/workflows/pint.yml` | halaman Actions di GitHub |
| Kode mengikuti pedoman | seluruh `app/`, `tests/`, `database/`, `routes/` | periksa nama, tipe, visibilitas, PHPDoc pada berkas mana pun |
| Dokumentasi internal | PHPDoc pada setiap kelas dan method publik | buka berkas di `app/` |
