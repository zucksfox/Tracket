<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SparepartController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sparepart::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('part_code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->get('filter') === 'critical') {
            $query->where('stock', '<=', 2);
        }

        $categories = Sparepart::select('category')->distinct()->pluck('category');
        $spareparts = $query->orderBy('stock', 'asc')->paginate(15)->withQueryString();
        $criticalCount = Sparepart::where('stock', '<=', 2)->count();

        return view('spareparts.index', compact('spareparts', 'categories', 'criticalCount'));
    }

    public function create(): View
    {
        return view('spareparts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'part_code' => ['required', 'string', 'max:50', 'unique:spareparts,part_code'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
        ], [
            'part_code.required' => 'Kode suku cadang wajib diisi.',
            'part_code.unique' => 'Kode suku cadang sudah terdaftar.',
            'name.required' => 'Nama komponen suku cadang wajib diisi.',
            'stock.min' => 'Jumlah stok tidak boleh bernilai negatif.',
            'sell_price.min' => 'Harga jual tidak boleh bernilai negatif.',
        ]);

        Sparepart::create($validated);

        return redirect()->route('spareparts.index')->with('success', 'Suku cadang baru berhasil didaftarkan.');
    }

    public function edit(Sparepart $sparepart): View
    {
        return view('spareparts.edit', compact('sparepart'));
    }

    public function update(Request $request, Sparepart $sparepart): RedirectResponse
    {
        $validated = $request->validate([
            'part_code' => ['required', 'string', 'max:50', 'unique:spareparts,part_code,' . $sparepart->id],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
        ]);

        $sparepart->update($validated);

        return redirect()->route('spareparts.index')->with('success', 'Data suku cadang berhasil diperbarui.');
    }

    public function destroy(Sparepart $sparepart): RedirectResponse
    {
        if ($sparepart->serviceOrderParts()->count() > 0) {
            return back()->with('error', 'Suku cadang ini tidak dapat dihapus karena tercatat dalam transaksi pengerjaan servis.');
        }

        $sparepart->delete();
        return redirect()->route('spareparts.index')->with('success', 'Suku cadang berhasil dihapus dari sistem.');
    }
}
