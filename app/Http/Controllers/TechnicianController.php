<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manajemen akun teknisi dan pengguna backoffice lainnya.
 */
class TechnicianController extends Controller
{
    /**
     * Daftar teknisi (dan admin) beserta beban pengerjaan aktifnya.
     */
    public function index(Request $request): View
    {
        $query = User::query()->withCount([
            'assignedServices as active_services_count' => function ($q) {
                $q->whereIn('status', ['pending', 'diagnosing', 'in_progress', 'ready']);
            },
        ]);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($request->get('role'), ['admin', 'cashier', 'technician'], true)) {
            $query->where('role', $request->get('role'));
        }

        $technicians = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('technicians.index', compact('technicians'));
    }

    public function create(): View
    {
        return view('technicians.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
            'role' => ['required', 'in:technician,admin,cashier'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email sudah dipakai akun lain.',
            'password.required' => 'Kata sandi awal wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'role.required' => 'Peran akun wajib dipilih.',
            'role.in' => 'Peran akun tidak valid.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // auto-hash via casts()
            'role' => $validated['role'],
        ]);

        return redirect()->route('technicians.index')->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(User $technician): View
    {
        return view('technicians.edit', ['technician' => $technician]);
    }

    public function update(Request $request, User $technician): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,'.$technician->id],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
            'role' => ['required', 'in:technician,admin,cashier'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email sudah dipakai akun lain.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'role.required' => 'Peran akun wajib dipilih.',
            'role.in' => 'Peran akun tidak valid.',
        ]);

        $technician->name = $validated['name'];
        $technician->email = $validated['email'];
        $technician->role = $validated['role'];
        if (! empty($validated['password'])) {
            $technician->password = $validated['password']; // auto-hash via casts()
        }
        $technician->save();

        return redirect()->route('technicians.index')->with('success', 'Data akun pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $technician): RedirectResponse
    {
        if ($technician->id === (int) auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        if (User::where('role', 'admin')->count() <= 1 && $technician->isAdmin()) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya akun administrator.');
        }

        // service_orders.technician_id memakai nullOnDelete():
        // riwayat servis tetap ada, hanya lepas dari penanggung jawab.
        $technician->delete();

        return redirect()->route('technicians.index')->with('success', 'Akun pengguna berhasil dihapus. Riwayat servis yang pernah ditangani tetap tersimpan.');
    }
}
