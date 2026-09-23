@extends('layouts.app')

@section('title', 'Ubah Suku Cadang')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
    <div>
        <a href="{{ route('spareparts.index') }}" class="text-[12px] hover:underline" style="color: var(--act);">Kembali ke Katalog Suku Cadang</a>
        <h1 class="text-[20px] font-bold t-ink tracking-tight mt-1">Perbarui Data Suku Cadang</h1>
        <p class="text-[12.5px] t-muted mt-0.5">Ubah spesifikasi, stok fisik, atau penyesuaian harga jual komponen.</p>
    </div>

    <div class="panel p-6">
        <form action="{{ route('spareparts.update', $sparepart) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="part_code" class="block mb-1">Kode Suku Cadang <span style="color: var(--rose);">*</span></label>
                    <input type="text" id="part_code" name="part_code" value="{{ old('part_code', $sparepart->part_code) }}" required
                        placeholder="contoh: LCD-IPH13" class="field mono">
                </div>
                <div>
                    <label for="category" class="block mb-1">Kategori Komponen <span style="color: var(--rose);">*</span></label>
                    <input type="text" id="category" name="category" value="{{ old('category', $sparepart->category) }}" required
                        placeholder="contoh: Layar / LCD, Baterai, Keyboard" class="field">
                </div>
            </div>

            <div>
                <label for="name" class="block mb-1">Nama Suku Cadang Lengkap <span style="color: var(--rose);">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $sparepart->name) }}" required
                    placeholder="contoh: LCD Screen OLED iPhone 13 Original" class="field">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="stock" class="block mb-1">Jumlah Stok <span style="color: var(--rose);">*</span></label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', $sparepart->stock) }}" min="0" required class="field mono">
                </div>
                <div>
                    <label for="buy_price" class="block mb-1">Harga Beli (Modal) <span style="color: var(--rose);">*</span></label>
                    <input type="number" id="buy_price" name="buy_price" value="{{ old('buy_price', (int)$sparepart->buy_price) }}" min="0" step="1000" required class="field mono">
                </div>
                <div>
                    <label for="sell_price" class="block mb-1">Harga Jual (Tarif) <span style="color: var(--rose);">*</span></label>
                    <input type="number" id="sell_price" name="sell_price" value="{{ old('sell_price', (int)$sparepart->sell_price) }}" min="0" step="1000" required class="field mono">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3" style="border-top: 1px solid var(--line-soft);">
                <a href="{{ route('spareparts.index') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-act">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
