<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // 1. Service Metrics
        $pendingCount = ServiceOrder::where('status', 'pending')->count();
        $inProgressCount = ServiceOrder::whereIn('status', ['diagnosing', 'in_progress'])->count();
        $readyCount = ServiceOrder::where('status', 'ready')->count();
        $completedThisMonth = ServiceOrder::where('status', 'completed')
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->count();

        // 2. Financial Metrics (Admin Only)
        $revenueThisMonth = 0;
        if ($user->isAdmin()) {
            $revenueThisMonth = ServiceOrder::where('status', 'completed')
                ->whereMonth('updated_at', Carbon::now()->month)
                ->whereYear('updated_at', Carbon::now()->year)
                ->sum('total_cost');
        }

        // 3. Critical Spareparts (stock <= 2)
        $criticalParts = Sparepart::where('stock', '<=', 2)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();
        $criticalPartsCount = Sparepart::where('stock', '<=', 2)->count();

        // 4. Recent Service Orders
        $recentQuery = ServiceOrder::with(['customer', 'technician'])->latest();
        if ($user->isTechnician()) {
            // For technicians, prioritize showing ones assigned to them or unassigned
            $recentQuery->where(function ($q) use ($user) {
                $q->where('technician_id', $user->id)
                  ->orWhereNull('technician_id');
            });
        }
        $recentServices = $recentQuery->take(8)->get();

        // 5. Total customers count
        $totalCustomers = Customer::count();

        return view('dashboard.index', compact(
            'pendingCount',
            'inProgressCount',
            'readyCount',
            'completedThisMonth',
            'revenueThisMonth',
            'criticalParts',
            'criticalPartsCount',
            'recentServices',
            'totalCustomers'
        ));
    }
}
