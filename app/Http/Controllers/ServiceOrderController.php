<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderPart;
use App\Models\Sparepart;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ServiceOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = ServiceOrder::with(['customer', 'technician'])->latest();

        // Filter status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter my tasks for technician
        if ($request->boolean('my_tasks') && auth()->user()->isTechnician()) {
            $query->where('technician_id', auth()->id());
        }

        // Search query
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('service_code', 'like', "%{$s}%")
                    ->orWhere('device_name', 'like', "%{$s}%")
                    ->orWhere('device_serial', 'like', "%{$s}%")
                    ->orWhereHas('customer', function ($cq) use ($s) {
                        $cq->where('name', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%");
                    });
            });
        }

        $services = $query->paginate(12)->withQueryString();

        // Status counts for badge summary
        $counts = [
            'all' => ServiceOrder::count(),
            'pending' => ServiceOrder::where('status', 'pending')->count(),
            'diagnosing' => ServiceOrder::where('status', 'diagnosing')->count(),
            'in_progress' => ServiceOrder::where('status', 'in_progress')->count(),
            'ready' => ServiceOrder::where('status', 'ready')->count(),
            'completed' => ServiceOrder::where('status', 'completed')->count(),
        ];

        return view('services.index', compact('services', 'counts'));
    }

    public function create(): View
    {
        $technicians = User::whereIn('role', ['admin', 'technician'])->orderBy('name')->get();

        return view('services.create', compact('technicians'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Customer selection or inline new creation
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:100'],
            'customer_phone' => ['required_without:customer_id', 'nullable', 'string', 'max:20'],
            'customer_address' => ['nullable', 'string', 'max:500'],

            // Device details
            'device_name' => ['required', 'string', 'max:150'],
            'device_serial' => ['nullable', 'string', 'max:100'],
            'issue_description' => ['required', 'string'],
            'accessories_included' => ['nullable', 'string', 'max:255'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'technician_id' => ['nullable', Rule::exists('users', 'id')->whereIn('role', ['admin', 'technician'])],
        ], [
            'device_name.required' => 'Nama & tipe perangkat wajib diisi.',
            'issue_description.required' => 'Deskripsi keluhan kerusakan wajib dicatat.',
            'customer_name.required_without' => 'Nama pelanggan wajib diisi jika pelanggan belum terdaftar.',
            'customer_phone.required_without' => 'Nomor telepon pelanggan wajib diisi jika pelanggan belum terdaftar.',
        ]);

        // Check-in memakai format lokal yang konsisten: angka saja, awalan 08,
        // tanpa +62, 62, spasi, titik, atau tanda hubung. Operator bebas;
        // nomor Indonesia memang tidak boleh divalidasi berdasarkan prefix tertentu.
        $phone = null;
        if (empty($validated['customer_id']) && ! empty($validated['customer_phone'])) {
            $phone = $validated['customer_phone'];
            if (! preg_match('/^08[0-9]{8,11}$/', $phone)) {
                return back()
                    ->with('error', 'Format nomor HP tidak dikenal. Gunakan awalan 08 tanpa tanda hubung, contoh: 081234567890.')
                    ->withInput();
            }
        }

        $serviceOrder = DB::transaction(function () use ($validated, $phone) {
            // Find or create customer
            if (! empty($validated['customer_id'])) {
                $customerId = $validated['customer_id'];
            } else {
                // Cegah duplikasi pelanggan akibat beda penulisan nomor
                // (0812..., 62812..., +62...).
                $customer = Customer::where('phone', $phone)
                    ->orWhere('phone', '62'.ltrim($phone, '0'))
                    ->orWhere('phone', '0'.ltrim($phone, '0'))
                    ->first();

                if ($customer) {
                    // Pelanggan sudah terdaftar: pakai data DB apa adanya.
                    // Jangan timpa nama/alamat lama dengan input baru yang
                    // bisa saja salah ketik saat buru-buru.
                    $customerId = $customer->id;
                } else {
                    $newCustomer = Customer::create([
                        'name' => $validated['customer_name'],
                        'phone' => $phone,
                        'address' => $validated['customer_address'] ?? null,
                    ]);
                    $customerId = $newCustomer->id;
                }
            }

            // Generate sequential code with concurrency lock
            $serviceCode = ServiceOrder::generateServiceCode();

            $laborCost = (float) ($validated['labor_cost'] ?? 0);

            return ServiceOrder::create([
                'service_code' => $serviceCode,
                'customer_id' => $customerId,
                'technician_id' => $validated['technician_id'] ?? null,
                'device_name' => $validated['device_name'],
                'device_serial' => $validated['device_serial'] ?? null,
                'issue_description' => $validated['issue_description'],
                'accessories_included' => $validated['accessories_included'] ?? null,
                'status' => 'pending',
                'labor_cost' => $laborCost,
                'total_cost' => $laborCost,
                'warranty_days' => 0,
                'warranty_expires_at' => null,
            ]);
        });

        return redirect()->route('services.show', $serviceOrder)
            ->with('success', "Penerimaan servis berhasil dicatat. Nomor Nota: {$serviceOrder->service_code}");
    }

    public function show(ServiceOrder $serviceOrder): View
    {
        $serviceOrder->load(['customer', 'technician', 'orderParts.sparepart']);
        // Part yang tersedia bisa dipasang; part stok 0 tetap ditampilkan
        // sebagai pilihan disabled agar teknisi tahu partnya TERDAFTAR
        // tapi HABIS, bukan belum diinput admin.
        $availableSpareparts = Sparepart::orderBy('category')->orderBy('name')->get();
        $technicians = User::whereIn('role', ['admin', 'technician'])->orderBy('name')->get();

        return view('services.show', compact('serviceOrder', 'availableSpareparts', 'technicians'));
    }

    /**
     * Single Action Progression Handler (Pending -> Diagnosing -> In Progress -> Ready)
     */
    public function updateStatus(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,diagnosing,in_progress,ready,completed,cancelled'],
            'technician_notes' => ['nullable', 'string'],
            'technician_id' => ['nullable', Rule::exists('users', 'id')->whereIn('role', ['admin', 'technician'])],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($request->user()?->isTechnician() && in_array($validated['status'], ['completed', 'cancelled'], true)) {
            abort(403, 'Teknisi harus menyerahkan proses selesai dan pembatalan kepada Admin/Kasir.');
        }

        DB::transaction(function () use (&$serviceOrder, $validated) {
            $serviceOrder = $this->lockEditableOrder($serviceOrder);
            $next = ['pending' => 'diagnosing', 'diagnosing' => 'in_progress', 'in_progress' => 'ready'];
            abort_unless($validated['status'] === $serviceOrder->status || ($next[$serviceOrder->status] ?? null) === $validated['status'], 422, 'Perubahan status tidak diizinkan. Gunakan checkout untuk pembayaran atau aksi pembatalan.');
            $serviceOrder->status = $validated['status'];

            if (isset($validated['technician_notes'])) {
                $serviceOrder->technician_notes = $validated['technician_notes'];
            }

            if (isset($validated['technician_id'])) {
                $serviceOrder->technician_id = $validated['technician_id'];
            }

            if (isset($validated['labor_cost'])) {
                $serviceOrder->labor_cost = (float) $validated['labor_cost'];
            }

            $serviceOrder->save();
            $serviceOrder->recalculateTotal();

        });

        return back()->with('success', "Status servis {$serviceOrder->service_code} berhasil diperbarui menjadi: ".$serviceOrder->status_meta['label']);
    }

    /**
     * Attach Sparepart & Deduct Stock with DB Transaction & Concurrency Lock
     */
    public function addPart(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $validated = $request->validate([
            'sparepart_id' => ['required', 'exists:spareparts,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'sparepart_id.required' => 'Pilih suku cadang yang akan dipasang.',
            'quantity.min' => 'Jumlah minimal adalah 1 unit.',
        ]);

        try {
            DB::transaction(function () use ($serviceOrder, $validated) {
                $serviceOrder = $this->lockEditableOrder($serviceOrder);
                // Lock sparepart row for update
                $sparepart = Sparepart::where('id', $validated['sparepart_id'])->lockForUpdate()->firstOrFail();

                $requestedQty = (int) $validated['quantity'];

                // Strict Stock Validation (Algoritma Pengurangan Stok)
                if ($sparepart->stock < $requestedQty) {
                    throw new \Exception("Stok suku cadang [{$sparepart->name}] tidak mencukupi! Tersedia: {$sparepart->stock} unit.");
                }

                // Deduct stock
                $sparepart->stock -= $requestedQty;
                $sparepart->save();

                // Check if part already in order, increment qty or create new
                $existingPart = ServiceOrderPart::where('service_order_id', $serviceOrder->id)
                    ->where('sparepart_id', $sparepart->id)
                    ->first();

                if ($existingPart) {
                    $existingPart->quantity += $requestedQty;
                    $existingPart->subtotal = $existingPart->quantity * (float) $sparepart->sell_price;
                    $existingPart->save();
                } else {
                    ServiceOrderPart::create([
                        'service_order_id' => $serviceOrder->id,
                        'sparepart_id' => $sparepart->id,
                        'quantity' => $requestedQty,
                        'unit_price' => $sparepart->sell_price,
                        'subtotal' => $requestedQty * (float) $sparepart->sell_price,
                    ]);
                }

                // Recalculate totals
                $serviceOrder->recalculateTotal();
            });

            return back()->with('success', 'Suku cadang berhasil ditambahkan dan stok gudang otomatis terpotong.');
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove Part & Rollback Stock
     */
    public function removePart(ServiceOrder $serviceOrder, ServiceOrderPart $orderPart): RedirectResponse
    {
        DB::transaction(function () use ($serviceOrder, $orderPart) {
            $serviceOrder = $this->lockEditableOrder($serviceOrder);
            $orderPart = $serviceOrder->orderParts()->whereKey($orderPart->id)->lockForUpdate()->firstOrFail();
            // Restore stock to sparepart
            $sparepart = Sparepart::where('id', $orderPart->sparepart_id)->lockForUpdate()->first();
            if ($sparepart) {
                $sparepart->stock += $orderPart->quantity;
                $sparepart->save();
            }

            // Delete order part
            $orderPart->delete();

            // Recalculate totals
            $serviceOrder->recalculateTotal();
        });

        return back()->with('success', 'Komponen suku cadang berhasil dilepas dan stok telah dikembalikan ke inventaris.');
    }

    /**
     * Checkout: Complete Service & Activate Warranty
     */
    public function checkout(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        abort_unless($request->user()?->canCheckout(), 403);

        $validated = $request->validate([
            'warranty_days' => ['required', 'integer', 'min:0', 'max:3650'],
            'payment_method' => ['required', 'in:cash,qr'],
            'confirm_payment' => ['nullable', 'boolean'],
            'technician_notes' => ['nullable', 'string'],
        ], [
            'warranty_days.required' => 'Durasi garansi wajib diisi (isi 0 bila tanpa garansi).',
        ]);

        if ($validated['payment_method'] === 'qr' && ! $request->boolean('confirm_payment')) {
            return back()->withInput()->with('error', 'Pembayaran belum dikonfirmasi. Periksa transfer masuk sebelum menandai lunas.');
        }

        $days = (int) $validated['warranty_days'];
        $expiryDate = $days > 0 ? Carbon::today()->addDays($days)->toDateString() : null;

        DB::transaction(function () use (&$serviceOrder, $validated, $days, $expiryDate) {
            $serviceOrder = ServiceOrder::whereKey($serviceOrder->id)->lockForUpdate()->firstOrFail();
            abort_unless($serviceOrder->status === 'ready', 422, 'Checkout hanya untuk servis siap diambil.');
            $serviceOrder->payment_status = 'paid';
            $serviceOrder->payment_method = $validated['payment_method'];
            $serviceOrder->paid_at = now();
            $serviceOrder->status = 'completed';
            // Tarif jasa SELALU dibaca dari DB (satu sumber kebenaran) yang sudah
            // disimpan lewat form "Catatan Teknisi". Tidak lagi menerima nilai
            // dari hidden input yang bisa basi saat halaman terbuka lama.
            $serviceOrder->labor_cost = (float) $serviceOrder->labor_cost;
            $serviceOrder->warranty_days = $days;
            $serviceOrder->warranty_expires_at = $expiryDate;
            if (auth()->user()->canRepair() && ! empty($validated['technician_notes'])) {
                $serviceOrder->technician_notes = $validated['technician_notes'];
            }
            $serviceOrder->save();
            $serviceOrder->recalculateTotal();

        });

        return redirect()->route('services.show', $serviceOrder)
            ->with('success', 'Unit berhasil diserahkan ke pelanggan! Tagihan akhir Rp '.number_format($serviceOrder->total_cost, 0, ',', '.').". Masa garansi {$days} hari aktif hingga ".($expiryDate ? Carbon::parse($expiryDate)->isoFormat('D MMMM Y') : 'tanpa garansi').'.');
    }

    /**
     * Cancel Order & Automatic Rollback of All Attached Spareparts
     */
    public function cancel(Request $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Hanya Admin/Kasir yang dapat membatalkan servis dan mengembalikan stok.');

        $returnedSummary = [];

        DB::transaction(function () use ($serviceOrder, &$returnedSummary) {
            $serviceOrder = $this->lockEditableOrder($serviceOrder);
            // Rollback stock for all attached parts
            foreach ($serviceOrder->orderParts as $part) {
                $sparepart = Sparepart::where('id', $part->sparepart_id)->lockForUpdate()->first();
                if ($sparepart) {
                    $sparepart->stock += $part->quantity;
                    $sparepart->save();
                    $returnedSummary[] = "{$sparepart->name} ({$part->quantity} unit)";
                }
            }

            $serviceOrder->status = 'cancelled';
            $serviceOrder->save();
        });

        $detail = count($returnedSummary) > 0
            ? ' Dikembalikan ke stok: '.implode(', ', $returnedSummary).'.'
            : ' Tidak ada suku cadang yang perlu dikembalikan.';

        return redirect()->route('services.show', $serviceOrder)
            ->with('info', 'Pengerjaan servis dibatalkan.'.$detail);
    }

    /**
     * Print View: Check-In Receipt
     */
    private function lockEditableOrder(ServiceOrder $serviceOrder): ServiceOrder
    {
        $locked = ServiceOrder::whereKey($serviceOrder->id)->lockForUpdate()->firstOrFail();
        abort_if(in_array($locked->status, ['completed', 'cancelled'], true), 422, 'Servis sudah ditutup dan tidak dapat diubah.');

        return $locked;
    }

    public function printReceipt(ServiceOrder $serviceOrder): View
    {
        $serviceOrder->load(['customer', 'technician']);

        return view('services.print_receipt', compact('serviceOrder'));
    }

    /**
     * Print View: Official Invoice & Warranty Certificate
     */
    public function printInvoice(ServiceOrder $serviceOrder): View
    {
        $serviceOrder->load(['customer', 'technician', 'orderParts.sparepart']);

        return view('services.print_invoice', compact('serviceOrder'));
    }
}
