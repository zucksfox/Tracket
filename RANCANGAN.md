# Rancangan &amp; Implementasi — Tracket

Dokumen ini memetakan rancangan ke kode yang benar-benar ada, lalu menunjukkan
bukti pemenuhan setiap butir ketentuan ujian praktik. Semua nomor baris dapat
diverifikasi langsung di repositori.

## 1. Identitas aplikasi

| Butir | Isi |
|---|---|
| Nama | Tracket — Sistem Manajemen Bengkel Servis &amp; Garansi |
| Bahasa | PHP 8.2 (dijalankan pada 8.2.12) |
| Kerangka kerja | Laravel 12.69.2 |
| Basis data | MySQL 8 (produksi), SQLite in-memory (pengujian) |
| Antarmuka | Blade + Tailwind 4 melalui Vite 7 (tanpa framework JS) |
| Pengujian | PHPUnit 11.5 — 138 tes, 499 asersi |
| Gaya kode | Laravel Pint (PSR-12), konfigurasi `pint.json` |

## 2. Pemodelan dan rancangan

### 2.1 Diagram relasi antar-entitas

```
customers                                 users
+---------------------+                   +----------------------+
| id            PK    |                   | id             PK    |
| name          100   |                   | name                 |
| phone         20 UQ |                   | email          UQ    |
| address       text  |                   | password (hash)      |
| timestamps          |                   | role: admin |        |
| deleted_at (soft)   |                   |       cashier |      |
+----------+----------+                   |       technician     |
           |                              | timestamps           |
           | 1                            +----------+-----------+
           |                                         | 1
           | N                                       | N (teknisi,
           v                                         v    boleh null)
+---------------------------------------------------------------+
| service_orders                                                |
| id PK, service_code 30 UQ, customer_id FK, technician_id FK    |
| device_name 150, device_serial 100, issue_description text     |
| accessories_included 255                                       |
| status ENUM(pending, diagnosing, in_progress, ready,           |
|             completed, cancelled) INDEX                        |
| labor_cost DECIMAL(12,2), total_cost DECIMAL(12,2)             |
| payment_status 20 INDEX, payment_method 20, paid_at            |
| warranty_days INT, warranty_expires_at DATE                    |
| technician_notes text, timestamps, deleted_at (soft)           |
+-----------------------------+---------------------------------+
                              | 1
                              | N
                              v
                    +---------------------------------+
                    | service_order_parts             |
                    | id PK, service_order_id FK      |
                    | sparepart_id FK, quantity INT   |
                    | unit_price, subtotal (12,2)     |
                    +----------------+----------------+
                                     | N
                                     | 1
                                     v
                    +---------------------------------+
                    | spareparts                      |
                    | id PK, part_code 50 UQ          |
                    | name 150, category 100          |
                    | stock INT, buy_price, sell_price|
                    | timestamps, deleted_at (soft)   |
                    +---------------------------------+

activity_logs  (tabel audit tunggal untuk tiga model di atas)
+---------------------------------------------------------------+
| id PK, subject_type + subject_id  -> relasi polymorphic        |
| action 50 (created|updated|deleted), causer_id FK users        |
| properties text (cast array: {new: {...}, old: {...}})         |
| timestamps; INDEX(subject_type, subject_id, action)            |
+---------------------------------------------------------------+
```

Keputusan pemodelan yang perlu dijelaskan saat demo:

1. **Harga disimpan, bukan dirujuk.** `service_order_parts.unit_price` dan
   `subtotal` menyimpan harga pada saat pemasangan. Bila harga katalog naik
   besok, nota dan laporan lama tidak ikut berubah.
2. **`technician_id` boleh null.** Servis dapat masuk antrian sebelum ada
   teknisi yang ditugaskan. Penghapusan akun teknisi memakai `nullOnDelete`
   agar riwayat servis tidak ikut hilang.
3. **`sparepart_id` memakai `restrictOnDelete`.** Suku cadang yang pernah
   dipakai tidak boleh dihapus permanen, karena akan memutus jejak nota.
4. **Status memakai ENUM + INDEX.** Nilai status dibatasi basis data, dan
   filter dashboard/laporan (yang selalu memfilter status) terlayani indeks.
5. **Nomor nota `SRV-YYYYMM-XXXX`** dibuat berurutan per bulan di dalam
   transaksi dengan `lockForUpdate()`, sehingga dua kasir yang menyimpan
   bersamaan tidak mendapat nomor yang sama.

### 2.2 Struktur lapisan

```
routes/web.php                     peta seluruh alamat (66 baris, baca dari sini)
  |
  +-- app/Http/Middleware/         auth, role:admin,cashier, role:admin,
  |                                private-cache (NoStoreAuthenticated)
  |
  +-- app/Http/Controllers/        10 controller, semuanya extends Controller
  |     ReportController, ReportExportController, ServiceOrderController, ...
  |
  +-- app/Actions/                 logika yang dipakai bersama
  |     DateRangeFilter            validasi rentang tanggal (maks 93 hari)
  |     ReportQuery                satu sumber filter untuk layar + ekspor
  |
  +-- app/Exports/                 pemrosesan laporan dan berkas
  |     ReportColumnMap            susunan kolom (array dua dimensi)
  |     ReportExportService        baca data bertahap (chunk/do-while)
  |     ReportExporterRegistry     daftar format yang tersedia
  |     ExportArchiver             tulis/baca berkas di media penyimpanan
  |     CsvReportExporter  \
  |     JsonReportExporter  > dua implementasi dari satu antarmuka
  |
  +-- app/Contracts/
  |     ExportableReport           antarmuka format ekspor
  |
  +-- app/Models/                  Eloquent, casts, relasi, accessor
  |     Concerns/LogsActivity      trait audit untuk semua model
  |
  +-- resources/views/             25 berkas Blade (input &amp; output pengguna)
```

## 3. Pemenuhan ketentuan ujian praktik

### a. Program sesuai rancangan

Rancangan ditulis lebih dahulu di `design.md` (token warna, tipografi, layout)
dan `UX_UI_AUDIT.md` (temuan dan perbaikannya), lalu `README.md` menjelaskan
alur kerja. Bagian 2 dokumen ini adalah pemetaan rancangan ke kode.

Bukti kesesuaian: `resources/views/layouts/theme.blade.php` mendefinisikan
token warna dengan nama yang sama persis seperti tabel di `design.md`
(`--act`, `--paper`, `--line`, `--muted`, dan seterusnya), sehingga komponen
tidak menebak warna sendiri.

### b. Pedoman penulisan kode (coding guidelines)

* **PSR-12** ditegakkan otomatis oleh Laravel Pint. Konfigurasi ada di
  `pint.json`, mengunci preset `laravel`.
* Perintah pemeriksaan dan perbaikan:

```bash
php vendor/bin/pint --test     # melaporkan pelanggaran
php vendor/bin/pint            # memperbaiki otomatis
```

* Hasil terakhir: `{"tool":"pint","result":"passed"}` untuk seluruh
  `app/`, `tests/`, `database/`, dan `routes/`.
* Aturan lain yang dipegang: satu kelas satu berkas, nama kelas = nama berkas,
  deklarasi tipe pada seluruh parameter dan nilai kembali (`: void`,
  `: RedirectResponse`, `: array`), properti selalu diberi visibilitas
  eksplisit (`protected`, `private`).

### c. Antarmuka input dan output

Seluruh masukan melewati validasi server, dan seluruh keluaran berupa halaman
Blade yang dapat dicetak.

| Halaman | Berkas | Peran |
|---|---|---|
| Masuk | `resources/views/auth/login.blade.php` | publik |
| Dashboard | `resources/views/dashboard/index.blade.php` | semua |
| Check-in servis (input) | `resources/views/services/create.blade.php` | admin, kasir |
| Daftar &amp; detail servis | `services/index.blade.php`, `services/show.blade.php` | semua |
| Data pelanggan | `customers/index|create|edit` | admin |
| Suku cadang | `spareparts/index|create|edit` | admin, teknisi |
| Pengguna | `technicians/index|create|edit` | admin |
| Laporan | `reports/index.blade.php` | admin |
| Tanda terima (output cetak) | `services/print_receipt.blade.php` | semua |
| Faktur &amp; kartu garansi (cetak) | `services/print_invoice.blade.php` | semua |
| Portal lacak publik | `tracking/index|show|multiple` | tanpa login |
| Galat | `errors/404.blade.php`, `errors/419.blade.php` | publik |

Cara memperlihatkan ke asesor: isi formulir check-in, simpan, lalu tekan
tombol cetak — hasil cetak adalah keluaran berkas fisik berisi nomor nota yang
baru dibuat.

### d. Tipe data, sintaks, percabangan, dan pengulangan

**Tipe data** — setiap kolom basis data diberi tipe yang sesuai
(`DECIMAL(12,2)` untuk uang agar tidak ada galat pembulatan pecahan biner,
`DATE` untuk tanggal garansi, `ENUM` untuk status terbatas, `INT` untuk stok),
dan Eloquent melakukan konversi tipe saat data dibaca:

```php
// app/Models/ServiceOrder.php
protected function casts(): array
{
    return [
        'labor_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'warranty_days' => 'integer',
        'paid_at' => 'datetime',
        'warranty_expires_at' => 'date',
    ];
}
```

**Percabangan `if / else`** — contoh pada validasi format nomor telepon:

```php
// app/Http/Controllers/ServiceOrderController.php
if (! preg_match('/^08[0-9]{8,11}$/', $phone)) {
    return back()->with('error', 'Format nomor HP tidak dikenal. ...')->withInput();
}
```

**Percabangan `match`** (setara `switch`, tersedia sejak PHP 8) — dipakai
memetakan status servis ke label dan aksi berikutnya:

```php
// app/Models/ServiceOrder.php — getStatusMetaAttribute()
return match ($this->status) {
    'pending' => ['label' => 'Menunggu Diagnosa', 'bg' => 'stamp-pending', 'step' => 1],
    'diagnosing' => ['label' => 'Sedang Diagnosa', 'bg' => 'stamp-diagnosing', 'step' => 2],
    // ...
    default => ['label' => ucfirst($this->status), 'bg' => 'stamp-diagnosing', 'step' => 0],
};
```

**Pengulangan `for`** — grafik pendapatan harian pada dashboard:

```php
// app/Http/Controllers/DashboardController.php:31
for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
    $daily = $orders->get($day->toDateString(), collect());
    $chart[] = ['date' => $day->toDateString(), 'label' => $day->format('d/m'),
        'labor' => (float) $daily->sum('labor_cost'),
        'parts' => (float) $daily->sum('order_parts_sum_subtotal')];
}
```

**Pengulangan `foreach`** — 14 berkas, contoh perakitan kartu ringkasan status:

```php
// app/Http/Controllers/DashboardController.php:42
foreach ($statuses as $status => [$label, $color]) {
    $breakdown[] = ['label' => $label, 'color' => $color, 'count' => (int) ($counts[$status] ?? 0)];
}
```

**Pengulangan `do-while`** — pembacaan data laporan bertahap. Dipakai
`do-while`, bukan `while`, karena potongan pertama harus selalu diambil lebih
dahulu sebelum diketahui apakah masih ada potongan berikutnya:

```php
// app/Exports/ReportExportService.php
do {
    $chunk = (clone $query)->forPage($page, self::CHUNK_SIZE)->get();
    foreach ($chunk as $order) {
        $number++;
        $rows[] = $this->rowFor($order, $number);
    }
    $page++;
} while ($chunk->count() === self::CHUNK_SIZE && $page <= self::MAX_CHUNKS);
```

**Pengulangan `while` biasa** dipakai untuk memangkas daftar berkas arsip
supaya hanya sejumlah tertentu yang ditampilkan:

```php
// app/Exports/ExportArchiver.php
while (count($files) > $limit) {
    array_pop($files);
}
```

Selain itu Blade menyediakan `@foreach` dan `@forelse` untuk menggambar daftar
(tabel servis, kartu dashboard, baris nota), yang dipakai di 14 berkas tampilan.

### e. Prosedur, fungsi, dan method

Seluruh perilaku aplikasi berada di dalam method dengan nama yang menjelaskan
tujuan, dipanggil dari tempat lain. Contoh yang layak ditunjukkan:

| Method | Berkas | Kegunaan |
|---|---|---|
| `generateServiceCode(): string` | `ServiceOrder.php` | membuat nomor nota berurutan dengan penguncian baris |
| `recalculateTotal(): void` | `ServiceOrder.php` | menghitung ulang total dari jasa + suku cadang |
| `getWarrantyInfoAttribute(): array` | `ServiceOrder.php` | menghitung sisa masa garansi (accessor) |
| `isLowStock(): bool` | `Sparepart.php` | menandai stok kritis |
| `canCheckout(): bool` / `canRepair(): bool` | `User.php` | wewenang per peran |
| `fromRequest(Request): array` | `DateRangeFilter.php` | memvalidasi rentang tanggal |
| `build(Builder): array` | `ReportExportService.php` | menyusun tabel laporan |
| `store()` / `list()` / `read()` | `ExportArchiver.php` | menulis, mendaftar, membaca berkas |
| `lockEditableOrder(ServiceOrder): ServiceOrder` | `ServiceOrderController.php` | mengunci servis yang masih boleh diubah |

Semuanya memakai parameter bertipe dan nilai kembali bertipe, sesuai
rekomendasi pedoman PHP modern.

### f. Array dua dimensi dan alasannya

**1. Susunan kolom laporan — `app/Exports/ReportColumnMap.php`**

```php
return [
    'no' => ['label' => 'No', 'resolve' => fn (ServiceOrder $o, int $n) => $n],
    'service_code' => ['label' => 'Nomor Nota', 'resolve' => fn (ServiceOrder $o) => $o->service_code],
    // ...17 kolom
];
```

Dimensi pertama adalah nama kolom, dimensi kedua adalah keterangan kolom itu
(label tampilan dan closure pengambil nilainya). **Alasannya:** susunan kolom
laporan dan cara mengambil nilainya sering berubah bersamaan, sedangkan berkas
ekspor (CSV/JSON) hanya butuh daftar datar. Dengan memisahkan keterangan kolom
dari isi berkas, menambah atau memindahkan kolom cukup di satu tempat ini — dan
header tidak mungkin tidak sejajar dengan isi barisnya, karena keduanya
dibangkitkan dari sumber yang sama (dibuktikan tes
`test_column_map_header_matches_every_row_length`).

**2. Peta status dashboard — `app/Http/Controllers/DashboardController.php:38`**

```php
$statuses = ['pending' => ['Menunggu diagnosa', '#F5A742'],
    'diagnosing' => ['Diagnosa', '#6366F1'], /* ... */];
foreach ($statuses as $status => [$label, $color]) { /* ... */ }
```

Dimensi pertama adalah kunci status dari basis data, dimensi kedua adalah
[label, warna]. **Alasannya:** urutan kartu ringkasan harus selalu mengikuti
alur servis, bukan urutan abjad atau urutan hasil query, sehingga tampilan
tetap sama setiap kali halaman dibuka.

**3. Menu navigasi — `resources/views/layouts/app.blade.php:20`**

```php
$groups = [
    'OPERASIONAL' => [['Dashboard', route('dashboard'), 'dashboard', true], /* ... */],
    'INVENTORI' => [['Suku Cadang', route('spareparts.index'), 'parts', false]],
];
```

Dimensi pertama adalah judul kelompok menu, dimensi kedua adalah baris menu
[label, url, ikon, status aktif]. **Alasannya:** menu bertambah terus seiring
fitur baru; dengan bentuk ini menambah satu menu cukup satu baris, tanpa
mengubah perulangan yang menggambarnya.

**4. Langkah alur servis — `resources/views/services/show.blade.php:28`**

```php
$steps = [
    1 => ['key' => 'pending', 'name' => '1. Antrian'],
    2 => ['key' => 'diagnosing', 'name' => '2. Diagnosa'],
    // ...
];
```

**5. Cadangan ikon — `resources/views/layouts/icon.blade.php`** (peta nama
ikon → data SVG).

Array tiga dimensi dipakai pada penggabungan ketiga struktur di atas saat
merender: `$groups` (kelompok → baris menu → kolom baris). Contoh lain yang
sah: `resources/views/reports/index.blade.php` memakai peta status → label
untuk pilihan filter.

### g. Fasilitas menyimpan dan membaca data dari media penyimpanan

Dua lapis, keduanya dapat diperlihatkan langsung:

**1. Basis data.** Enam tabel dengan kunci asing, indeks, dan soft delete.
Perintah: `php artisan migrate`. Data tersimpan permanen di MySQL dan dibaca
kembali pada setiap permintaan.

**2. Berkas di media penyimpanan server.** Fitur ekspor laporan menulis berkas
nyata ke `storage/app/private/exports/`, lalu berkas itu didaftar dan dibaca
kembali dari disk.

Bukti keluaran nyata (dijalankan pada basis data demo berisi 6 servis):

```
Kolom: 17 | Baris: 6
Header: No | Nomor Nota | Pembaruan Terakhir | Pelanggan | Nomor HP |
        Perangkat | Nomor Seri | Teknisi | Status | Biaya Jasa |
        Biaya Sparepart | Total | Status Bayar | Metode Bayar | Waktu Bayar |
        Garansi (hari) | Garansi Berakhir
Berkas: laporan-servis-2026-09-01-2026-09-25-20260925-071952.csv
        (677 byte, 2 baris selesai)
Dibaca ulang: 677 byte
```

Isi berkas yang diunduh ulang melalui `GET /reports/export/{file}`:

```
No;Nomor Nota;Pembaruan Terakhir;Pelanggan;Nomor HP;Perangkat;Nomor Seri;
Teknisi;Status;Biaya Jasa;Biaya Sparepart;Total;Status Bayar;Metode Bayar;
Waktu Bayar;Garansi (hari);Garansi Berakhir
1;SRV-202609-0002;25/09/2026 05:58;Siti Rahmawati;085678901234;
ASUS TUF Gaming A15 FA506;N7NRCX001923;Budi Santoso (Teknisi Utama);
Selesai & Diambil;250000.00;1200000.00;1450000.00;Lunas;QR;
25/09/2026 05:58;30;25/10/2026
```

Detail teknis yang layak disebut: berkas CSV diawali BOM UTF-8 dan memakai
pemisah titik koma agar terbuka rapi di Microsoft Excel berbahasa Indonesia;
angka ditulis dengan titik desimal agar tetap terbaca mesin; nama berkas
memuat cap waktu sehingga ekspor berikutnya tidak menimpa berkas sebelumnya;
dan nama berkas disaring sebelum dibaca sehingga tidak dapat dipakai untuk
menunjuk berkas lain di luar folder arsip (diuji oleh
`test_download_rejects_path_traversal_attempt`).

### h. Hak akses, properti, pewarisan, polymorphism, overloading, dan antarmuka

**Hak akses tipe data.** Properti selalu diberi visibilitas eksplisit:
`protected $fillable`, `protected $hidden` (menyembunyikan `password` dan
`remember_token` dari keluaran array/JSON), `private const` pada
`ExportArchiver` dan `ReportExportService`, serta `private readonly` pada
`ReportExporterRegistry` dan `ExportArchiver`. Method internal ditandai
`protected` (mis. `writeLog()` pada trait) dan hanya dapat dipanggil dari
dalam kelasnya.

**Properti.** Setiap model Eloquent mendeklarasikan properti konfigurasinya
(`$fillable`, `$hidden`, `$casts`), dan kelas pemroses menyimpan
ketergantungannya sebagai properti konstruktor:

```php
// app/Exports/ExportArchiver.php
public function __construct(private readonly Filesystem $disk) {}
```

**Pewarisan (inheritance).**

```
Illuminate\Database\Eloquent\Model
  +-- App\Models\Customer, Sparepart, ServiceOrder, ServiceOrderPart, ActivityLog
Illuminate\Foundation\Auth\User
  +-- App\Models\User
Illuminate\Database\Eloquent\Model + trait LogsActivity
  +-- Customer, Sparepart, ServiceOrder   (jejak audit otomatis)
App\Http\Controllers\Controller (abstract)
  +-- 10 controller konkret
App\Exports\AbstractReportExporter (abstract)
  +-- CsvReportExporter, JsonReportExporter
```

`Controller` dan `AbstractReportExporter` sengaja dibuat `abstract`: keduanya
tidak bermakna bila dibuat langsung, tetapi menetapkan perilaku bersama untuk
kelas turunannya. `AbstractReportExporter` menyediakan `fileName()` dan
`cell()` yang sudah jadi, sehingga subkelas hanya menulis bagian yang
benar-benar berbeda (`extension()`, `mimeType()`, `render()`).

**Polymorphism.** Dua bentuk yang dapat ditunjukkan:

1. *Polymorphism saat kompilasi-ulang (pewarisan method).* `fileName()`
   didefinisikan sekali di `AbstractReportExporter` dan berlaku sama untuk
   kedua format; `render()` didefinisikan berbeda di tiap format.
2. *Polymorphism saat aplikasi berjalan (lewat antarmuka).*
   `ReportExportController` hanya mengenal tipe `ExportableReport`:

```php
$exporter = $registry->make($validated['format']);   // CsvReportExporter atau JsonReportExporter
$contents = $exporter->render($header, $rows);       // isi method dipilih saat berjalan
```

   Controller tidak pernah menyebut nama kelas format secara langsung, jadi
   menambah format Excel nanti tidak mengubah satu baris pun di controller.

3. *Polymorphism relasi data.* `ActivityLog::subject()` memakai `morphTo()`,
   sehingga satu baris log dapat menunjuk ke `Customer`, `Sparepart`, atau
   `ServiceOrder` tanpa kolom tambahan:

```php
$log = ActivityLog::where('subject_id', $customer->id)->first();
$log->subject;   // otomatis berupa model Customer
```

**Overloading.** PHP tidak mengizinkan dua method bernama sama dengan jumlah
parameter berbeda seperti Java, sehingga variasi pemanggilan disediakan lewat
parameter opsional dan variadic — inilah bentuk overloading yang dikenal di
PHP:

```php
// app/Exports/ReportExporterRegistry.php — satu method, dua cara memanggil
public function make(string $format, ?ExportableReport $fallback = null): ExportableReport

// app/Http/Middleware/RoleMiddleware.php — jumlah peran bebas
public function handle(Request $request, Closure $next, ...$roles): Response

// app/Exports/ExportArchiver.php
public function list(int $limit = 8): array
```

**Antarmuka (interface).** `App\Contracts\ExportableReport` mendeklarasikan
lima method yang wajib dimiliki setiap format ekspor:

```php
interface ExportableReport
{
    public function extension(): string;
    public function mimeType(): string;
    public function fileName(string $basename): string;
    public function render(array $header, array $rows): string;
}
```

Diimplementasikan oleh `CsvReportExporter` dan `JsonReportExporter` (keduanya
lewat `AbstractReportExporter`). Karena controller hanya bergantung pada
antarmuka ini, keduanya dapat dipertukarkan tanpa mengubah controller.

### i. Dua namespace atau lebih

Autoload PSR-4 memetakan enam namespace aplikasi (lihat `composer.json`):

| Namespace | Folder | Isi |
|---|---|---|
| `App\Models` | `app/Models` | 6 model Eloquent |
| `App\Models\Concerns` | `app/Models/Concerns` | trait `LogsActivity` |
| `App\Http\Controllers` | `app/Http/Controllers` | 10 controller |
| `App\Http\Middleware` | `app/Http/Middleware` | 2 middleware |
| `App\Actions` | `app/Actions` | `DateRangeFilter`, `ReportQuery` |
| `App\Exports` | `app/Exports` | 7 kelas pemrosesan laporan |
| `App\Contracts` | `app/Contracts` | antarmuka `ExportableReport` |
| `App\Providers` | `app/Providers` | `AppServiceProvider` |
| `Database\Seeders` | `database/seeders` | data awal |
| `Tests\Unit`, `Tests\Feature` | `tests` | 138 tes |

### j. Pustaka eksternal

| Pustaka | Versi | Pemanfaatan |
|---|---|---|
| `laravel/framework` | ^12.0 | kerangka kerja utama |
| `laravel/tinker` | ^2.10 | konsol interaktif untuk pemeriksaan data |
| `nesbot/carbon` | bawaan Laravel | perhitungan tanggal dan masa garansi |
| `fakerphp/faker` | ^1.23 | data palsu untuk pengujian |
| `phpunit/phpunit` | ^11.5 | kerangka pengujian |
| `mockery/mockery` | ^1.6 | objek tiruan pengujian |
| `laravel/pint` | ^1.24 | pemeriksa gaya kode |
| `tailwindcss` + `vite` | ^4 / ^7 | pemrosesan aset antarmuka |

### k. Basis data

Sembilan berkas migrasi, enam tabel (ditambah tiga tabel bawaan Laravel untuk
sesi, antrian, dan cache):

```
0001_01_01_000000_create_users_table
0001_01_01_000001_create_cache_table
0001_01_01_000002_create_jobs_table
2026_09_22_120807_create_customers_table
2026_09_22_120808_create_service_orders_table
2026_09_22_120808_create_spareparts_table
2026_09_22_120809_create_service_order_parts_table
2026_09_24_000001_add_soft_deletes_and_activity_log
2026_09_25_000002_add_cashier_and_payment_fields
2026_09_25_100000_normalize_activity_log_subject_type
```

Yang layak disebut saat demo: kunci asing dengan perilaku berbeda per relasi
(`cascadeOnDelete`, `nullOnDelete`, `restrictOnDelete`), `softDeletes()` pada
tiga tabel, indeks gabungan pada `activity_logs`, dan migrasi tambahan yang
**tidak menghapus data lama** (`php artisan migrate`, bukan `migrate:fresh`).

### l. Dokumentasi sesuai pedoman

| Berkas | Isi |
|---|---|
| `README.md` | gambaran produk, fitur, cara instalasi, alur kerja |
| `design.md` | sistem desain: token warna, tipografi, layout |
| `UX_UI_AUDIT.md` | temuan audit antarmuka dan perbaikannya |
| `PANDUAN_DEMO.md` | langkah demo per peran, akun, catatan basis data |
| `update.md`, `IMPLEMENTASI_UPDATE.md` | catatan perubahan versi |
| `RANCANGAN.md` | dokumen ini — pemetaan rancangan ke kode |
| PHPDoc | seluruh kelas, method publik, dan properti pada `app/` |

Bentuk PHPDoc yang dipakai mengikuti pedoman PHP: ringkasan satu baris,
keterangan tambahan bila perlu, lalu tag `@param`, `@return`, dan `@throws`.
Contoh pada method ekspor:

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

## 4. Pengujian

```bash
php artisan test
```

Hasil terakhir pada mesin pengembangan:

```
Tests:    138 passed (499 assertions)
Duration: 11.30s
```

Cakupan pengujian yang berhubungan dengan dokumen ini:

| Berkas | Yang diuji |
|---|---|
| `tests/Unit/ReportExportTest.php` | antarmuka ekspor, isi berkas CSV/JSON, penyeragaman angka, registry, keselarasan header dengan baris |
| `tests/Feature/ReportExportWorkflowTest.php` | ekspor lewat HTTP, berkas benar-benar tertulis, unduh ulang, penolakan format tak dikenal, penolakan lintas peran, path traversal, relasi polymorphic log |
| `tests/Feature/ReportManagementTest.php` | ringkasan pendapatan, filter tanggal dan status |
| `tests/Feature/CashierWorkflowTest.php` | alur check-in sampai pembayaran dan garansi |
| `tests/Feature/ServiceOrderManagementTest.php` | perpindahan status dan pemasangan suku cadang |
| `tests/Unit/UserRolesTest.php` | wewenang per peran |

## 5. Cara menjalankan

```bash
composer install
cp .env.example .env          # lalu isi DB_DATABASE
php artisan key:generate
php artisan migrate           # JANGAN migrate:fresh bila data sudah ada
php artisan db:seed           # aman diulang (firstOrCreate)
npm install && npm run build
php artisan serve
```

Pemeriksaan menyeluruh sebelum demo:

```bash
php artisan test
php vendor/bin/pint --test
php artisan view:clear
```

## 6. Susunan berkas baru pada pembaruan ini

```
app/Contracts/ExportableReport.php                  antarmuka format ekspor
app/Exports/AbstractReportExporter.php              kelas dasar bersama
app/Exports/CsvReportExporter.php                   implementasi CSV
app/Exports/JsonReportExporter.php                  implementasi JSON
app/Exports/ReportExporterRegistry.php              daftar format + overloading
app/Exports/ReportColumnMap.php                     susunan kolom (array 2D)
app/Exports/ReportExportService.php                 pembacaan bertahap (do-while)
app/Exports/ExportArchiver.php                      tulis/baca berkas arsip
app/Actions/ReportQuery.php                         filter bersama layar + ekspor
app/Http/Controllers/ReportExportController.php     HTTP ekspor dan unduh
database/migrations/2026_09_25_100000_...php        penyeragaman subject_type log
tests/Unit/ReportExportTest.php                     8 tes
tests/Feature/ReportExportWorkflowTest.php          10 tes
pint.json                                           konfigurasi gaya kode
RANCANGAN.md                                        dokumen ini
```
