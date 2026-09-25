<?php

namespace App\Exports;

use App\Contracts\ExportableReport;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Daftar format ekspor yang tersedia.
 *
 * Peta format => kelas ini membuat penambahan format baru tidak menyentuh
 * controller: cukup tulis kelas ekspor baru dan daftarkan di sini.
 */
class ReportExporterRegistry
{
    /**
     * Peta format yang didukung. Kunci adalah nilai yang muncul di URL,
     * nilainya adalah kelas ekspor yang menanganinya.
     *
     * @var array<string, class-string<ExportableReport>>
     */
    private const EXPORTERS = [
        'csv' => CsvReportExporter::class,
        'json' => JsonReportExporter::class,
    ];

    public function __construct(private readonly Container $container) {}

    /**
     * Ambil penangan ekspor untuk satu format.
     *
     * Parameter $fallback bersifat opsional — inilah cara PHP menerapkan
     * "overloading": PHP tidak mengizinkan dua method bernama sama dengan
     * jumlah parameter berbeda seperti Java, sehingga variasi pemanggilan
     * disediakan lewat parameter opsional dan variadic.
     *
     * @throws InvalidArgumentException Bila format tidak dikenal.
     */
    public function make(string $format, ?ExportableReport $fallback = null): ExportableReport
    {
        $format = strtolower(trim($format));

        if (! isset(self::EXPORTERS[$format])) {
            if ($fallback instanceof ExportableReport) {
                return $fallback;
            }

            throw new InvalidArgumentException("Format ekspor \"{$format}\" tidak dikenal.");
        }

        return $this->container->make(self::EXPORTERS[$format]);
    }

    /**
     * Format yang benar-benar tersedia, dipakai untuk menyusun tombol di UI
     * supaya tombol tidak pernah menunjuk ke format yang tidak ada.
     *
     * @return array<int, string>
     */
    public function formats(): array
    {
        return array_keys(self::EXPORTERS);
    }

    public function supports(string $format): bool
    {
        return isset(self::EXPORTERS[strtolower(trim($format))]);
    }

    /**
     * Tebak penangan ekspor dari ekstensi sebuah nama berkas.
     *
     * Dipakai saat mengunduh ulang berkas arsip: dari namanya saja sudah dapat
     * diketahui MIME type yang harus dikirim, tanpa perlu mencatat format di
     * basis data.
     *
     * @throws InvalidArgumentException Bila ekstensi berkas tidak dikenal.
     */
    public function forFile(string $fileName): ExportableReport
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return $this->make($extension);
    }
}
