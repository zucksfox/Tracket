<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data induk pelanggan, termasuk pencarian cepat saat check-in servis.
 */
class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query()->withCount('serviceOrders');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^08[0-9]{8,11}$/', 'max:20', 'unique:customers,phone'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / telepon wajib diisi.',
            'phone.unique' => 'Nomor telepon sudah terdaftar pada pelanggan lain.',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^08[0-9]{8,11}$/', 'max:20', 'unique:customers,phone,'.$customer->id],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / telepon wajib diisi.',
            'phone.unique' => 'Nomor telepon sudah digunakan pelanggan lain.',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->serviceOrders()->count() > 0) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena memiliki riwayat servis terkait.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil dihapus.');
    }

    /**
     * AJAX Autocomplete Endpoint for Check-In Form
     */
    public function lookup(Request $request): JsonResponse
    {
        $phone = $request->get('phone');
        if (! $phone || strlen($phone) < 3) {
            return response()->json(['found' => false]);
        }

        // Normalize for exact-match lookup: 08xx, 628xx, +62-8xx → unified search
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $cleanPhone = preg_replace('/^(62|0)+/', '0', $cleanPhone);

        if (strlen($cleanPhone) < 8) {
            return response()->json(['found' => false]);
        }

        $customer = Customer::where('phone', $cleanPhone)
            ->orWhere('phone', '62'.ltrim($cleanPhone, '0'))
            ->orWhere('phone', '0'.ltrim($cleanPhone, '0'))
            ->first();

        if ($customer) {
            return response()->json([
                'found' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                ],
            ]);
        }

        return response()->json(['found' => false]);
    }
}
