<?php

namespace App\Actions;

use App\Models\ServiceOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Satu sumber kebenaran untuk filter laporan.
 *
 * Halaman Laporan dan proses ekspor memakai kelas yang sama, sehingga angka
 * yang terlihat di layar dan angka yang tertulis di berkas hasil ekspor tidak
 * mungkin berbeda — keduanya membaca filter dari objek ini.
 */
class ReportQuery
{
    /**
     * Status yang sah dipakai sebagai filter.
     *
     * @var array<int, string>
     */
    public const STATUSES = ['all', 'pending', 'diagnosing', 'in_progress', 'ready', 'completed', 'cancelled'];

    public function __construct(private readonly Request $request) {}

    /**
     * Rentang tanggal laporan (default: bulan berjalan, maksimal 93 hari).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function range(): array
    {
        return DateRangeFilter::fromRequest($this->request);
    }

    /**
     * Status terpilih; default "completed" karena laporan keuangan hanya
     * menghitung servis yang benar-benar selesai.
     */
    public function status(): string
    {
        $this->request->validate([
            'status' => ['nullable', 'in:'.implode(',', self::STATUSES)],
        ]);

        return $this->request->input('status') ?: 'completed';
    }

    /**
     * Daftar servis sesuai filter layar, sudah urut pasti (updated_at lalu id).
     *
     * Urutan yang pasti bukan sekadar kerapian tampilan: ekspor membaca data
     * bertahap memakai offset, dan tanpa urutan yang konsisten baris dapat
     * terlewat atau terhitung dua kali.
     *
     * @return Builder<ServiceOrder>
     */
    public function listed(): Builder
    {
        [$start, $end] = $this->range();
        $status = $this->status();

        $query = ServiceOrder::query()->whereBetween('updated_at', [$start, $end]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return $query
            ->with(['customer', 'technician'])
            ->withSum('orderParts', 'subtotal')
            ->orderByDesc('updated_at')
            ->orderByDesc('id');
    }

    /**
     * Hanya servis selesai, dipakai untuk ringkasan pendapatan.
     *
     * @return Builder<ServiceOrder>
     */
    public function completed(): Builder
    {
        [$start, $end] = $this->range();

        return ServiceOrder::query()
            ->whereBetween('updated_at', [$start, $end])
            ->where('status', 'completed');
    }
}
