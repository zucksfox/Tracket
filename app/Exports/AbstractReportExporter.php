<?php

namespace App\Exports;

use App\Contracts\ExportableReport;

/**
 * Kelas dasar bersama untuk seluruh format ekspor.
 *
 * Menyimpan perilaku yang sama di semua format (penamaan berkas dan
 * penyeragaman isi sel) supaya subkelas hanya perlu mengurus bagian yang
 * benar-benar berbeda, yaitu cara merangkai berkasnya.
 */
abstract class AbstractReportExporter implements ExportableReport
{
    /**
     * Penamaan berkas seragam untuk semua format.
     *
     * Method ini tidak ditimpa oleh subkelas: hanya ekstensinya yang berbeda,
     * dan itu diambil dari extension() milik masing-masing format.
     */
    public function fileName(string $basename): string
    {
        return $basename.'.'.$this->extension();
    }

    /**
     * Seragamkan satu sel menjadi teks yang aman untuk berkas.
     *
     * Nilai null menjadi string kosong (bukan tulisan "null"), dan angka
     * desimal selalu memakai titik sebagai pemisah supaya berkas hasil ekspor
     * dapat dibaca kembali oleh perangkat lunak apa pun.
     */
    protected function cell(string|int|float|null $value): string
    {
        return match (true) {
            $value === null => '',
            is_float($value) => number_format($value, 2, '.', ''),
            default => (string) $value,
        };
    }

    /**
     * Ubah satu baris data menjadi baris teks yang sudah diseragamkan.
     *
     * @param array<int, string|int|float|null> $row
     * @return array<int, string>
     */
    protected function cells(array $row): array
    {
        return array_map(fn ($value) => $this->cell($value), $row);
    }
}
