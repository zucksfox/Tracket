@extends('layouts.app')

@section('title', 'Data Pelanggan')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold t-ink tracking-tight">Pelanggan</h1>
            <p class="text-[13px] t-muted mt-0.5">Kelola daftar kontak pelanggan dan riwayat pengerjaan unit.</p>
        </div>
        <a href="{{ route('customers.create') }}" class="btn btn-act">Tambah Pelanggan Baru</a>
    </div>

    <div class="panel p-5">
        <form action="{{ route('customers.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input aria-label="Cari pelanggan" type="text" name="search" value="{{ request('search') }}" class="field"
                    placeholder="Cari berdasarkan nama atau no. telepon / WhatsApp...">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-act">Cari</button>
                @if(request('search'))
                    <a href="{{ route('customers.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="panel overflow-hidden p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
            <h2 class="text-base font-semibold t-ink">Daftar pelanggan</h2>
            <span class="text-[13px] t-muted">{{ $customers->total() }} hasil</span>
        </div>
        <div class="overflow-x-auto">
            <table class="sheet">
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>No. WhatsApp / Telepon</th>
                        <th>Alamat Domisili</th>
                        <th class="text-center">Riwayat Servis</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>
                                <div class="font-semibold t-body">{{ $customer->name }}</div>
                                <div class="tabular-nums text-[13px] t-muted">ID #CST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="tabular-nums">
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $customer->phone)) }}" target="_blank"
                                    class="hover:underline" style="color: var(--act);">
                                    {{ $customer->phone }}
                                </a>
                            </td>
                            <td class="t-muted max-w-xs truncate">{{ $customer->address ?: '-' }}</td>
                            <td class="text-center">
                                <span class="tabular-nums text-[13px] font-semibold t-ink px-2 py-0.5 rounded-lg" style="border: 1px solid var(--line);">{{ $customer->service_orders_count }} unit</span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-ghost" style="padding: 6px 10px; font-size: 13px;">Edit</a>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pelanggan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 13px;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center t-muted">Belum ada data pelanggan yang sesuai dengan kriteria pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4" style="border-top: 1px solid var(--line-soft);">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
