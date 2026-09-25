<?php

namespace App\Contracts;

use App\Exports\AbstractReportExporter;
use App\Exports\ReportExporterRegistry;

/**
 * Kontrak untuk setiap format ekspor laporan.
 *
 * Semua kelas yang mengimplementasikan antarmuka ini dapat dipakai bergantian
 * oleh ReportExportController tanpa controller perlu mengetahui kelas
 * konkretnya (runtime polymorphism). Menambah format baru cukup membuat satu
 * kelas baru dan mendaftarkannya di ReportExporterRegistry — controller dan
 * tampilan tidak perlu diubah.
 *
 * @see AbstractReportExporter
 * @see ReportExporterRegistry
 */
interface ExportableReport
{
    /**
     * Ekstensi berkas hasil ekspor, tanpa titik. Contoh: "csv".
     */
    public function extension(): string;

    /**
     * MIME type yang dikirim pada header Content-Type.
     */
    public function mimeType(): string;

    /**
     * Nama berkas unduhan lengkap dengan ekstensi.
     *
     * @param string $basename Nama dasar tanpa ekstensi, contoh "laporan-servis-2026-09-01".
     */
    public function fileName(string $basename): string;

    /**
     * Bentuk isi berkas dari data tabular yang diberikan.
     *
     * Format data yang diterima bersifat seragam supaya seluruh implementasi
     * dapat dipertukarkan: header adalah array satu dimensi berisi judul
     * kolom, sedangkan rows adalah array dua dimensi berisi baris data dengan
     * urutan kolom yang sama seperti header.
     *
     * @param array<int, string> $header Judul kolom.
     * @param array<int, array<int, string|int|float|null>> $rows Baris data.
     * @return string Isi berkas siap dikirim ke peramban.
     */
    public function render(array $header, array $rows): string;
}
