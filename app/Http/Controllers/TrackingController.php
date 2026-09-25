<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ServiceOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Portal pelacakan publik; dapat dibuka pelanggan tanpa login.
 */
class TrackingController extends Controller
{
    public function index(): View
    {
        return view('tracking.index');
    }

    public function search(Request $request): View|RedirectResponse
    {
        $query = trim($request->get('query', ''));

        if (empty($query)) {
            return redirect()->route('tracking.index')
                ->with('error', 'Silakan masukkan Nomor Servis (contoh: SRV-202609-0001) atau Nomor WhatsApp Anda.');
        }

        // Auto-detect format: Service Code vs Phone
        $cleanPhone = preg_replace('/[^0-9]/', '', $query);

        if (str_starts_with(strtoupper($query), 'SRV-')) {
            // Direct service code lookup
            $order = ServiceOrder::with(['customer', 'orderParts.sparepart', 'technician'])
                ->where('service_code', strtoupper($query))
                ->first();

            if (! $order) {
                return view('tracking.index', [
                    'searchedQuery' => $query,
                    'errorMessage' => "Nomor servis [{$query}] tidak ditemukan di sistem. Mohon periksa kembali huruf dan angka pada nota tanda terima Anda.",
                ]);
            }

            return view('tracking.show', compact('order'));
        }

        // Phone number lookup: exact match only (privasi pelanggan).
        // Coba format 08xx, 628xx, dan 28xx agar pengetikan dari nota
        // tetap cocok tanpa membuka data pelanggan lain.
        if (strlen($cleanPhone) >= 8) {
            $customer = Customer::where('phone', $cleanPhone)
                ->orWhere('phone', '62'.ltrim($cleanPhone, '0'))
                ->orWhere('phone', '0'.ltrim($cleanPhone, '0'))
                ->first();

            if ($customer) {
                $orders = ServiceOrder::where('customer_id', $customer->id)
                    ->latest()
                    ->get();

                if ($orders->count() === 1) {
                    return redirect()->route('tracking.show', $orders->first()->service_code);
                }

                if ($orders->count() > 1) {
                    return view('tracking.multiple', [
                        'customer' => $customer,
                        'orders' => $orders,
                        'searchedQuery' => $query,
                    ]);
                }
            }
        }

        // If not found by phone, try searching service code as fallback.
        // Prefix match only, and require the SRV- prefix so a partial phone
        // number can never accidentally resolve to someone else's service.
        if (str_starts_with(strtoupper($query), 'SRV-')) {
            $fallbackOrder = ServiceOrder::where('service_code', 'like', strtoupper($query).'%')->first();
            if ($fallbackOrder) {
                return redirect()->route('tracking.show', $fallbackOrder->service_code);
            }
        }

        return view('tracking.index', [
            'searchedQuery' => $query,
            'errorMessage' => "Data servis tidak ditemukan untuk [{$query}]. Pastikan nomor servis atau nomor WhatsApp yang Anda masukkan sudah benar.",
        ]);
    }

    public function show(string $service_code): View
    {
        $order = ServiceOrder::with(['customer', 'orderParts.sparepart', 'technician'])
            ->where('service_code', strtoupper($service_code))
            ->firstOrFail();

        return view('tracking.show', compact('order'));
    }
}
