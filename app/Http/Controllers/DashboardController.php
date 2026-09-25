<?php

namespace App\Http\Controllers;

use App\Actions\DateRangeFilter;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Ringkasan operasional harian: antrian aktif, pendapatan bulan berjalan,
 * stok kritis, dan grafik pendapatan per hari.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        [$start, $end] = DateRangeFilter::fromRequest($request);
        $isAdmin = $request->user()->isAdmin();
        $scope = ServiceOrder::query();
        if ($request->user()->isTechnician()) {
            $scope->where('technician_id', $request->user()->id);
        }
        $activeCount = (clone $scope)->whereIn('status', ['pending', 'diagnosing', 'in_progress', 'ready'])->count();
        $monthCompleted = (clone $scope)->where('status', 'completed')->whereBetween('updated_at', [now()->startOfMonth(), now()]);
        $completedThisMonth = (clone $monthCompleted)->count();
        $revenueThisMonth = $isAdmin ? (float) (clone $monthCompleted)->sum('total_cost') : null;
        $criticalPartsCount = Sparepart::where('stock', '<=', 2)->count();
        $chart = [];
        if ($isAdmin) {
            $orders = (clone $scope)->where('status', 'completed')->whereBetween('updated_at', [$start, $end])
                ->withSum('orderParts', 'subtotal')->get()->groupBy(fn ($order) => $order->updated_at->toDateString());
            for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                $daily = $orders->get($day->toDateString(), collect());
                $chart[] = ['date' => $day->toDateString(), 'label' => $day->format('d/m'),
                    'labor' => (float) $daily->sum('labor_cost'), 'parts' => (float) $daily->sum('order_parts_sum_subtotal')];
            }
        }
        $counts = (clone $scope)->whereBetween('created_at', [$start, $end])->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        // Array dua dimensi berpasangan kunci => [label, warna]: dimensi pertama
        // adalah kunci status dari basis data, dimensi kedua adalah keterangan
        // tampilannya. Disusun begitu supaya urutan kartu ringkasan di dashboard
        // selalu tetap (sesuai alur servis) tanpa perlu mengurutkan ulang hasil
        // query, dan warna tiap status tidak tersebar di beberapa berkas.
        $statuses = ['pending' => ['Menunggu diagnosa', '#F5A742'], 'diagnosing' => ['Diagnosa', '#6366F1'],
            'in_progress' => ['Dikerjakan', '#7C3AED'], 'ready' => ['Siap diambil', '#14B8A6'],
            'completed' => ['Selesai', '#1E7A5C'], 'cancelled' => ['Dibatalkan', '#E2574C']];
        $breakdown = [];
        foreach ($statuses as $status => [$label, $color]) {
            $breakdown[] = ['label' => $label, 'color' => $color, 'count' => (int) ($counts[$status] ?? 0)];
        }
        $warranties = (clone $scope)->with('customer')->where('status', 'completed')
            ->whereDate('warranty_expires_at', '>=', today())->orderBy('warranty_expires_at')->limit(5)->get();

        return view('dashboard.index', compact('isAdmin', 'start', 'end', 'activeCount', 'completedThisMonth',
            'revenueThisMonth', 'criticalPartsCount', 'chart', 'breakdown', 'warranties'));
    }
}
