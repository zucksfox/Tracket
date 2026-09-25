<?php

namespace App\Exports;

/**
 * Ekspor laporan ke berkas JSON.
 *
 * Berbeda dari CSV yang menyimpan baris sebagai larik berurutan, JSON di sini
 * memakai array_combine sehingga setiap baris menjadi objek dengan nama kolom
 * sebagai kunci. Bentuk ini yang dipakai bila data akan diolah lagi oleh
 * program lain, karena pembaca tidak perlu menebak urutan kolom.
 */
class JsonReportExporter extends AbstractReportExporter
{
    public function extension(): string
    {
        return 'json';
    }

    public function mimeType(): string
    {
        return 'application/json; charset=UTF-8';
    }

    public function render(array $header, array $rows): string
    {
        $records = [];

        foreach ($rows as $row) {
            $cells = $this->cells($row);

            // Jaga-jaga bila jumlah sel tidak sepadan dengan jumlah kolom:
            // array_combine menolak larik dengan panjang berbeda.
            if (count($cells) !== count($header)) {
                continue;
            }

            $records[] = array_combine($header, $cells);
        }

        return (string) json_encode([
            'columns' => $header,
            'total' => count($records),
            'rows' => $records,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
