<?php

namespace App\Exports;

use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Membaca data laporan dari basis data dan menyusunnya menjadi tabel siap
 * ekspor (header + baris).
 *
 * Data dibaca bertahap per potongan (chunk), bukan sekaligus, supaya laporan
 * dengan rentang tanggal panjang tidak menghabiskan memori. Pembacaan
 * bertahap inilah yang memakai struktur pengulangan do-while: potongan
 * berikutnya baru diminta setelah potongan sebelumnya selesai diproses, dan
 * pengulangan berhenti begitu potongan terakhir tidak lagi penuh.
 */
class ReportExportService
{
    /**
     * Jumlah baris yang dibaca dari basis data per putaran.
     */
    private const CHUNK_SIZE = 200;

    /**
     * Batas aman jumlah putaran (20.000 baris). Bila terlampaui, pengguna
     * diminta mempersempit rentang tanggal daripada server menahan permintaan
     * terlalu lama.
     */
    private const MAX_CHUNKS = 100;

    /**
     * Susun tabel laporan dari query yang diberikan.
     *
     * Query pemanggil wajib sudah memuat relasi customer dan technician serta
     * agregat orderParts (withSum) dan sudah memiliki urutan (orderBy) yang
     * pasti — urutan yang pasti diperlukan karena pembacaan bertahap memakai
     * offset, dan tanpa urutan yang konsisten baris dapat terlewat atau
     * terhitung dua kali.
     *
     * @param Builder<ServiceOrder> $query
     * @return array{0: array<int, string>, 1: array<int, array<int, string|int|float|null>>}
     *
     * @throws RuntimeException Bila jumlah baris melampaui batas aman.
     */
    public function build(Builder $query): array
    {
        $rows = [];
        $page = 1;
        $number = 0;

        do {
            /** @var Collection<int, ServiceOrder> $chunk */
            $chunk = (clone $query)->forPage($page, self::CHUNK_SIZE)->get();

            foreach ($chunk as $order) {
                $number++;
                $rows[] = $this->rowFor($order, $number);
            }

            $page++;
        } while ($chunk->count() === self::CHUNK_SIZE && $page <= self::MAX_CHUNKS);

        if ($chunk->count() === self::CHUNK_SIZE && $page > self::MAX_CHUNKS) {
            throw new RuntimeException(
                'Data laporan terlalu banyak untuk diekspor sekaligus (lebih dari '
                .self::CHUNK_SIZE * self::MAX_CHUNKS
                .' baris). Persempit rentang tanggal atau pilih satu status saja.'
            );
        }

        return [ReportColumnMap::header(), $rows];
    }

    /**
     * Ambil nilai satu baris sesuai urutan kolom pada ReportColumnMap.
     *
     * @return array<int, string|int|float|null>
     */
    private function rowFor(ServiceOrder $order, int $number): array
    {
        $cells = [];

        foreach (ReportColumnMap::definitions() as $column) {
            // Closure boleh hanya menerima satu parameter; parameter kedua
            // (nomor urut) tetap dikirim dan diabaikan bila tidak dipakai.
            $cells[] = ($column['resolve'])($order, $number);
        }

        return $cells;
    }
}
