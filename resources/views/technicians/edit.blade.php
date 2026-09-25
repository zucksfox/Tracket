@extends('layouts.app')

@section('title', 'Ubah Data Teknisi')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
    <div>
        <a href="{{ route('technicians.index') }}" class="text-[13px] hover:underline" style="color: var(--act);">Kembali ke Data Teknisi</a>
        <h1 class="text-2xl font-bold t-ink tracking-tight mt-1">Edit pengguna</h1>
        <p class="text-[13px] t-muted mt-0.5">Ubah identitas, peran, atau atur ulang kata sandi akun #USR-{{ str_pad($technician->id, 3, '0', STR_PAD_LEFT) }}.</p>
    </div>

    <div class="panel p-6">
        <form action="{{ route('technicians.update', $technician) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block mb-1">Nama Lengkap <span style="color: var(--rose);">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $technician->name) }}" required
                    placeholder="contoh: Budi Santoso" class="field">
            </div>

            <div>
                <label for="email" class="block mb-1">Alamat Email (untuk Login) <span style="color: var(--rose);">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $technician->email) }}" required
                    placeholder="contoh: budi@tracket.test" class="field tabular-nums">
            </div>

            <div>
                <label for="password" class="block mb-1">Kata Sandi Baru <span class="t-muted font-normal">(Kosongkan jika tidak diubah)</span></label>
                <input type="password" id="password" name="password" minlength="8"
                    placeholder="isi untuk mengatur ulang kata sandi" class="field tabular-nums" autocomplete="new-password">
                <span class="text-[13px] t-muted mt-1 block">Biarkan kosong untuk mempertahankan kata sandi saat ini. Minimal 8 karakter bila diisi.</span>
            </div>

            <div>
                <label for="role" class="block mb-1">Peran Akun <span style="color: var(--rose);">*</span></label>
                <select id="role" name="role" required class="field">
                    <option value="cashier" {{ old('role', $technician->role) === 'cashier' ? 'selected' : '' }}>Kasir, check-in, pembayaran dan cetak dokumen</option>
                    <option value="technician" {{ old('role', $technician->role) === 'technician' ? 'selected' : '' }}>Teknisi Servis, mengerjakan antrian dan diagnosa unit</option>
                    <option value="admin" {{ old('role', $technician->role) === 'admin' ? 'selected' : '' }}>Administrator, akses penuh termasuk master data dan laporan</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3" style="border-top: 1px solid var(--line-soft);">
                <a href="{{ route('technicians.index') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-act">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
