@extends('layouts.app')

@section('title', 'Tambah Pelanggan Baru')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
    <div>
        <a href="{{ route('customers.index') }}" class="text-[12px] hover:underline" style="color: var(--act);">Kembali ke Data Pelanggan</a>
        <h1 class="text-[20px] font-bold t-ink tracking-tight mt-1">Formulir Registrasi Pelanggan</h1>
        <p class="text-[12.5px] t-muted mt-0.5">Lengkapi identitas pelanggan untuk pencatatan nota dan notifikasi servis.</p>
    </div>

    <div class="panel p-6">
        <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block mb-1">Nama Lengkap Pelanggan <span style="color: var(--rose);">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    placeholder="contoh: Hendra Pratama" class="field">
            </div>

            <div>
                <label for="phone" class="block mb-1">Nomor WhatsApp / HP Aktif <span style="color: var(--rose);">*</span></label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                    placeholder="contoh: 081234567890" class="field mono">
                <span class="text-[11px] t-muted mt-1 block">Nomor ini digunakan pelanggan untuk melacak status servis pada portal publik.</span>
            </div>

            <div>
                <label for="address" class="block mb-1">Alamat Domisili <span class="t-muted font-normal">(Opsional)</span></label>
                <textarea id="address" name="address" rows="3"
                    placeholder="contoh: Jl. Merdeka No. 12, Kel. Menteng, Jakarta Pusat" class="field">{{ old('address') }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3" style="border-top: 1px solid var(--line-soft);">
                <a href="{{ route('customers.index') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-act">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</div>
@endsection
