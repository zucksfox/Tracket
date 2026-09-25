<?php

namespace App\Http\Controllers;

use App\Actions\ReportQuery;
use App\Exports\ExportArchiver;
use App\Exports\ReportExporterRegistry;
use App\Models\ServiceOrderPart;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman Laporan: ringkasan pendapatan dan riwayat transaksi.
 *
 * Ringkasan selalu dihitung dari servis selesai, sedangkan daftar di bawahnya
 * mengikuti filter status yang dipilih operator.
 */
class ReportController extends Controller
{
    public function index(
        Request $request,
        ReportQuery $report,
        ExportArchiver $archiver,
        ReportExporterRegistry $registry,
    ): View {
        abort_unless($request->user()->isAdmin(), 403);

        [$start, $end] = $report->range();
        $status = $report->status();

        // Ringkasan selalu dihitung dari servis selesai, meskipun daftar di
        // bawahnya boleh difilter ke status lain. Filter daftar dan dasar
        // perhitungan sengaja dipisah supaya angka pendapatan tidak berubah
        // hanya karena operator sedang melihat antrian.
        $completed = $report->completed();
        $laborTotal = (float) (clone $completed)->sum('labor_cost');
        $revenueTotal = (float) (clone $completed)->sum('total_cost');
        $partsTotal = (float) ServiceOrderPart::whereIn('service_order_id', (clone $completed)->select('id'))->sum('subtotal');
        $transactionCount = (clone $completed)->count();

        $transactions = $report->listed()->paginate(20)->withQueryString();

        $exportFormats = $registry->formats();
        $exportFiles = $archiver->list();

        return view('reports.index', compact(
            'start', 'end', 'laborTotal', 'partsTotal', 'revenueTotal',
            'transactionCount', 'transactions', 'status', 'exportFormats', 'exportFiles',
        ));
    }
}
