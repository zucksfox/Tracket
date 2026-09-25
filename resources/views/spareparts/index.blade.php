@extends('layouts.app')

@section('title', 'Katalog Suku Cadang')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold t-ink tracking-tight">Suku cadang</h1>
            <p class="text-[13px] t-muted mt-0.5">Kelola persediaan, pantau stok kritis, dan perbarui harga komponen.</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('spareparts.create') }}" class="btn btn-act">Tambah Suku Cadang</a>
        @endif
    </div>

    @if($criticalCount > 0)
        <div class="banner banner-amber flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="text-[13px] font-bold">Stok perlu ditambah</div>
                <div class="text-[13px] mt-0.5">Terdapat <strong>{{ $criticalCount }}</strong> jenis suku cadang dengan stok menipis (2 unit atau kurang).</div>
            </div>
            <a href="{{ route('spareparts.index', ['filter' => 'critical']) }}" class="btn btn-ink shrink-0">Filter Stok Kritis</a>
        </div>
    @endif

    <div class="panel p-5">
        <form action="{{ route('spareparts.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input aria-label="Cari suku cadang" type="text" name="search" value="{{ request('search') }}" class="field"
                    placeholder="Cari kode part, nama komponen, atau kategori...">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-act">Cari</button>
                @if(request('search') || request('filter'))
                    <a href="{{ route('spareparts.index') }}" class="btn btn-ghost">Reset Filter</a>
                @endif
            </div>
        </form>
    </div>

    <div class="panel overflow-hidden p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
            <h2 class="text-base font-semibold t-ink">Persediaan komponen</h2>
            <span class="text-[13px] t-muted">{{ $spareparts->total() }} hasil</span>
        </div>
        <div class="mobile-record-list">
            @forelse($spareparts as $part)
                <a href="{{ auth()->user()->isAdmin() ? route('spareparts.edit', $part) : route('spareparts.index') }}" class="mobile-record-card">
                    <div class="mobile-record-head"><span class="code-chip">{{ $part->part_code }}</span><span class="stamp {{ $part->stock === 0 ? 'stamp-cancelled' : ($part->isLowStock() ? 'stamp-pending' : 'stamp-ready') }}">{{ $part->stock === 0 ? 'Habis' : $part->stock . ' unit' }}</span></div>
                    <strong>{{ $part->name }}</strong><span>{{ $part->category }}</span><span class="mobile-record-muted">Harga jual Rp {{ number_format($part->sell_price, 0, ',', '.') }}</span>
                </a>
            @empty
                <div class="mobile-record-empty">Belum ada suku cadang terdaftar pada filter ini.</div>
            @endforelse
        </div>
        <div class="overflow-x-auto desktop-record-table">
            <table class="sheet">
                <thead>
                    <tr>
                        <th>Kode & Nama Suku Cadang</th>
                        <th>Kategori</th>
                        <th class="text-center">Sisa Stok</th>
                        @if(auth()->user()->isAdmin())
                        <th class="text-right">Harga Beli</th>
                        @endif
                        <th class="text-right">Harga Jual / Tarif</th>
                        @if(auth()->user()->isAdmin())
                        <th class="text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($spareparts as $part)
                        <tr>
                            <td>
                                <div class="font-semibold t-body">{{ $part->name }}</div>
                                <span class="tabular-nums text-[13px]" style="color: var(--warranty);">{{ $part->part_code }}</span>
                            </td>
                            <td>
                                <span class="text-[13px] t-ink px-2 py-0.5 rounded-lg" style="border: 1px solid var(--line);">{{ $part->category }}</span>
                            </td>
                            <td class="text-center">
                                @if($part->stock === 0)
                                    <span class="stamp stamp-cancelled">Habis (0)</span>
                                @elseif($part->isLowStock())
                                    <span class="stamp stamp-pending">Kritis ({{ $part->stock }})</span>
                                @else
                                    <span class="tabular-nums text-[13px] font-semibold t-body">{{ $part->stock }} unit</span>
                                @endif
                            </td>
                            @if(auth()->user()->isAdmin())
                            <td class="text-right tabular-nums t-muted">Rp {{ number_format($part->buy_price, 0, ',', '.') }}</td>
                            @endif
                            <td class="text-right tabular-nums font-semibold t-body">Rp {{ number_format($part->sell_price, 0, ',', '.') }}</td>
                            @if(auth()->user()->isAdmin())
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('spareparts.edit', $part) }}" class="btn btn-ghost" style="padding: 6px 10px; font-size: 13px;">Edit</a>
                                    <form action="{{ route('spareparts.destroy', $part) }}" method="POST" onsubmit="return confirm('Hapus suku cadang ini dari sistem?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 13px;">Hapus</button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 6 : 4 }}" class="py-10 text-center t-muted">Belum ada suku cadang terdaftar pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($spareparts->hasPages())
            <div class="p-4" style="border-top: 1px solid var(--line-soft);">
                {{ $spareparts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
