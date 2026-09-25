<?php

namespace App\Exports;

use App\Contracts\ExportableReport;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;

/**
 * Menyimpan berkas hasil ekspor ke media penyimpanan server dan membacanya
 * kembali.
 *
 * Setiap ekspor disimpan dengan cap waktu pada namanya, sehingga berkas lama
 * tidak tertimpa dan dapat diunduh ulang dari halaman Laporan. Daftar berkas
 * dibaca kembali dari disk saat halaman dibuka — inilah bagian "membaca data
 * dari media penyimpanan" yang dapat ditunjukkan langsung saat demo, terpisah
 * dari basis data.
 */
class ExportArchiver
{
    /**
     * Folder arsip di dalam disk aplikasi.
     */
    public const DIRECTORY = 'exports';

    public function __construct(private readonly Filesystem $disk) {}

    /**
     * Tulis isi ekspor ke disk dan kembalikan keterangan berkasnya.
     *
     * @param array<int, string> $header
     * @param array<int, array<int, string|int|float|null>> $rows
     * @return array{name: string, size: int, rows: int, created_at: string}
     */
    public function store(ExportableReport $exporter, string $basename, array $header, array $rows): array
    {
        $name = $exporter->fileName($basename.'-'.now()->format('Ymd-His'));
        $contents = $exporter->render($header, $rows);

        $this->disk->put(self::DIRECTORY.'/'.$name, $contents);

        return [
            'name' => $name,
            'size' => strlen($contents),
            'rows' => count($rows),
            'created_at' => now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * Daftar berkas arsip, terbaru lebih dahulu.
     *
     * @return array<int, array{name: string, size: int, created_at: string}>
     */
    public function list(int $limit = 8): array
    {
        $files = [];

        foreach ($this->disk->files(self::DIRECTORY) as $path) {
            $files[] = [
                'name' => basename($path),
                'size' => (int) $this->disk->size($path),
                'created_at' => Carbon::createFromTimestamp($this->disk->lastModified($path))->format('d/m/Y H:i'),
            ];
        }

        usort($files, fn (array $a, array $b) => strcmp($b['name'], $a['name']));

        // Buang kelebihan berkas dari urutan paling belakang (paling lama)
        // dengan pengulangan while, supaya daftar yang ditampilkan di halaman
        // tetap ringkas walau folder arsip sudah menumpuk.
        while (count($files) > $limit) {
            array_pop($files);
        }

        return $files;
    }

    /**
     * Baca kembali isi satu berkas arsip.
     *
     * Nama berkas disaring lebih dahulu supaya permintaan tidak dapat
     * menunjuk ke berkas lain di luar folder arsip.
     *
     * @throws \InvalidArgumentException Bila nama berkas tidak sah atau tidak ada.
     */
    public function read(string $name): string
    {
        $safe = basename($name);

        if ($safe !== $name || ! preg_match('/^[A-Za-z0-9._-]+$/', $safe)) {
            throw new \InvalidArgumentException('Nama berkas ekspor tidak sah.');
        }

        $path = self::DIRECTORY.'/'.$safe;

        if (! $this->disk->exists($path)) {
            throw new \InvalidArgumentException('Berkas ekspor tidak ditemukan.');
        }

        return (string) $this->disk->get($path);
    }

    public function path(string $name): string
    {
        return self::DIRECTORY.'/'.basename($name);
    }
}
