<?php

namespace App\Exports;

use App\Models\ServiceOrder;

/**
 * Definisi kolom laporan transaksi dalam satu tempat.
 *
 * Struktur di sini adalah array dua dimensi: dimensi pertama adalah nama
 * kolom, dimensi kedua adalah keterangan kolom tersebut (label tampilan dan
 * closure pengambil nilainya). Alasannya: susunan kolom laporan dan cara
 * mengambil nilainya sering berubah bersamaan, sedangkan berkas ekspor (CSV,
 * JSON) hanya butuh daftar datar. Dengan memisahkan "keterangan kolom" dari
 * "isi berkas", menambah atau memindahkan kolom cukup dilakukan di satu
 * tempat ini — dan tidak ada risiko header dan isi baris menjadi tidak
 * sejajar, karena keduanya dibangkitkan dari sumber yang sama.
 */
class ReportColumnMap
{
    /**
     * Susunan kolom laporan, urut dari kiri ke kanan.
     *
     * @return array<string, array{label: string, resolve: callable}>
     */
    public static function definitions(): array
    {
        return [
            'no' => [
                'label' => 'No',
                'resolve' => fn (ServiceOrder $order, int $number) => $number,
            ],
            'service_code' => [
                'label' => 'Nomor Nota',
                'resolve' => fn (ServiceOrder $order) => $order->service_code,
            ],
            'updated_at' => [
                'label' => 'Pembaruan Terakhir',
                'resolve' => fn (ServiceOrder $order) => $order->updated_at?->format('d/m/Y H:i'),
            ],
            'customer_name' => [
                'label' => 'Pelanggan',
                'resolve' => fn (ServiceOrder $order) => $order->customer?->name,
            ],
            'customer_phone' => [
                'label' => 'Nomor HP',
                'resolve' => fn (ServiceOrder $order) => $order->customer?->phone,
            ],
            'device_name' => [
                'label' => 'Perangkat',
                'resolve' => fn (ServiceOrder $order) => $order->device_name,
            ],
            'device_serial' => [
                'label' => 'Nomor Seri',
                'resolve' => fn (ServiceOrder $order) => $order->device_serial,
            ],
            'technician_name' => [
                'label' => 'Teknisi',
                'resolve' => fn (ServiceOrder $order) => $order->technician?->name,
            ],
            'status' => [
                'label' => 'Status',
                'resolve' => fn (ServiceOrder $order) => $order->status_meta['label'],
            ],
            'labor_cost' => [
                'label' => 'Biaya Jasa',
                'resolve' => fn (ServiceOrder $order) => (float) $order->labor_cost,
            ],
            'parts_subtotal' => [
                'label' => 'Biaya Sparepart',
                'resolve' => fn (ServiceOrder $order) => (float) ($order->order_parts_sum_subtotal ?? 0),
            ],
            'total_cost' => [
                'label' => 'Total',
                'resolve' => fn (ServiceOrder $order) => (float) $order->total_cost,
            ],
            'payment_status' => [
                'label' => 'Status Bayar',
                'resolve' => fn (ServiceOrder $order) => $order->payment_status === 'paid' ? 'Lunas' : 'Belum Dibayar',
            ],
            'payment_method' => [
                'label' => 'Metode Bayar',
                'resolve' => fn (ServiceOrder $order) => match ($order->payment_method) {
                    'cash' => 'Tunai',
                    'qr' => 'QR',
                    default => null,
                },
            ],
            'paid_at' => [
                'label' => 'Waktu Bayar',
                'resolve' => fn (ServiceOrder $order) => $order->paid_at?->format('d/m/Y H:i'),
            ],
            'warranty_days' => [
                'label' => 'Garansi (hari)',
                'resolve' => fn (ServiceOrder $order) => (int) $order->warranty_days,
            ],
            'warranty_expires_at' => [
                'label' => 'Garansi Berakhir',
                'resolve' => fn (ServiceOrder $order) => $order->warranty_expires_at?->format('d/m/Y'),
            ],
        ];
    }

    /**
     * Judul kolom untuk baris pertama berkas ekspor.
     *
     * @return array<int, string>
     */
    public static function header(): array
    {
        return array_values(array_map(
            fn (array $column) => $column['label'],
            self::definitions(),
        ));
    }
}
