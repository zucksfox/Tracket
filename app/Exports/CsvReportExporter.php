<?php

namespace App\Exports;

/**
 * Ekspor laporan ke berkas CSV.
 *
 * Pemisah kolom yang dipakai adalah titik koma (;), mengikuti pengaturan
 * regional Microsoft Excel berbahasa Indonesia yang memakai koma sebagai
 * pemisah desimal. Berkas diawali BOM UTF-8 supaya huruf beraksen dan
 * karakter non-ASCII tampil benar saat dibuka langsung di Excel.
 */
class CsvReportExporter extends AbstractReportExporter
{
    /**
     * Pemisah kolom berkas CSV.
     */
    private const DELIMITER = ';';

    public function extension(): string
    {
        return 'csv';
    }

    public function mimeType(): string
    {
        return 'text/csv; charset=UTF-8';
    }

    public function render(array $header, array $rows): string
    {
        // php://temp menampung berkas di memori selama masih kecil dan
        // otomatis berpindah ke berkas sementara bila tumbuh besar, sehingga
        // ekspor tetap aman untuk data bertahun-tahun.
        $handle = fopen('php://temp', 'r+');

        fwrite($handle, "\xEF\xBB\xBF"); // BOM UTF-8 untuk Excel
        fputcsv($handle, $header, self::DELIMITER);

        foreach ($rows as $row) {
            fputcsv($handle, $this->cells($row), self::DELIMITER);
        }

        rewind($handle);
        $contents = (string) stream_get_contents($handle);
        fclose($handle);

        return $contents;
    }
}
