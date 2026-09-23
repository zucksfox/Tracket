@extends('layouts.app')

@section('title', 'Dashboard Operasional')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[20px] font-bold t-ink leading-tight">Dashboard Operasional Servis</h1>
            <p class="text-[12.5px] t-muted mt-0.5">Monitoring alur pengerjaan unit, inventaris suku cadang, dan penyerahan bergaransi.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('services.create') }}" class="btn btn-act">+ Check-In Unit Baru</a>
            @endif
            <a href="{{ route('tracking.index') }}" target="_blank" class="btn btn-ghost">Portal Tracking Publik</a>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
        <a href="{{ route('services.index', ['status' => 'pending']) }}" class="statcard block hover:opacity-90">
            <div class="t-xs font-semibold t-ink mb-1">Antrian Masuk</div>
            <div class="statnum">{{ $pendingCount }}</div>
            <div class="t-[11px] t-muted mt-1">Perlu diagnosa awal</div>
        </a>
        <a href="{{ route('services.index', ['status' => 'in_progress']) }}" class="statcard block hover:opacity-90">
            <div class="t-xs font-semibold t-ink mb-1">Pengerjaan Aktif</div>
            <div class="statnum">{{ $inProgressCount }}</div>
            <div class="t-[11px] t-muted mt-1">Sedang ditangani teknisi</div>
        </a>
        <a href="{{ route('services.index', ['status' => 'ready']) }}" class="statcard block hover:opacity-90">
            <div class="t-xs font-semibold t-ink mb-1">Siap Diambil</div>
            <div class="statnum">{{ $readyCount }}</div>
            <div class="t-[11px] t-muted mt-1">Menunggu pemilik unit</div>
        </a>
        <a href="{{ route('services.index', ['status' => 'completed']) }}" class="statcard block hover:opacity-90">
            <div class="t-xs font-semibold t-ink mb-1">Selesai Bulan Ini</div>
            <div class="statnum">{{ $completedThisMonth }}</div>
            <div class="t-[11px] t-muted mt-1">Unit berhasil diserahkan</div>
        </a>
        @if(auth()->user()->isAdmin())
            <div class="statcard col-span-2 sm:col-span-4 lg:col-span-1">
                <div class="t-xs font-semibold t-ink mb-1.5">Omzet Bulan Ini</div>
                <div class="statnum">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</div>
                <div class="t-[11px] t-muted mt-1">Total jasa & sparepart</div>
            </div>
        @else
            <a href="{{ route('spareparts.index', ['filter' => 'critical']) }}" class="statcard col-span-2 sm:col-span-4 lg:col-span-1 block hover:opacity-90">
                <div class="t-xs font-semibold mb-1.5" style="color: var(--rose);">Stok Part Kritis</div>
                <div class="statnum">{{ $criticalPartsCount }}</div>
                <div class="t-[11px] t-muted mt-1">Stok 2 unit atau kurang</div>
            </a>
        @endif
    </div>

    @if($criticalPartsCount > 0)
        <div class="banner banner-amber flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="text-[12.5px] font-bold">Peringatan Kebutuhan Suku Cadang</div>
                <div class="text-[12px] mt-0.5" style="color: var(--body);">
                    Terdapat <strong>{{ $criticalPartsCount }}</strong> suku cadang dengan stok menipis (misal: {{ $criticalParts->pluck('name')->take(2)->implode(', ') }}).
                </div>
            </div>
            <a href="{{ route('spareparts.index', ['filter' => 'critical']) }}" class="btn btn-ink shrink-0">Cek & Restock Part</a>
        </div>
    @endif

    <div class="panel">
        <div class="panel-head p-4 flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-bold t-ink">Aktivitas Servis Terkini</h2>
                <p class="text-[12px] t-muted">Antrian dan pengerjaan unit yang baru diperbarui.</p>
            </div>
            <a href="{{ route('services.index') }}" class="text-[12px] font-semibold" style="color: var(--act);">Lihat Semua ({{ $pendingCount + $inProgressCount + $readyCount + $completedThisMonth }})</a>
        </div>

        <div class="overflow-x-auto">
            <table class="sheet">
                <thead>
                    <tr>
                        <th>No. Servis</th>
                        <th>Pelanggan</th>
                        <th>Perangkat & Gejala</th>
                        <th>Teknisi</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Tagihan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentServices as $srv)
                        <tr>
                            <td>
                                <a href="{{ route('services.show', $srv) }}" class="code-chip hover:underline">{{ $srv->service_code }}</a>
                                <div class="t-[11px] t-muted mono">{{ $srv->created_at->format('d/m H:i') }}</div>
                            </td>
                            <td>
                                <div class="font-semibold t-body">{{ $srv->customer->name }}</div>
                                <div class="mono t-[11px] t-muted">{{ $srv->customer->phone }}</div>
                            </td>
                            <td class="max-w-xs">
                                <div class="font-medium truncate">{{ $srv->device_name }}</div>
                                <div class="t-[11px] t-muted truncate">{{ $srv->issue_description }}</div>
                            </td>
                            <td>{{ $srv->technician ? $srv->technician->name : '-' }}</td>
                            <td class="text-center">
                                <span class="stamp {{ $srv->status_meta['bg'] }}">{{ $srv->status_meta['label'] }}</span>
                            </td>
                            <td class="text-right mono font-semibold t-body">Rp {{ number_format($srv->total_cost, 0, ',', '.') }}</td>
                            <td class="text-right">
                                <a href="{{ route('services.show', $srv) }}" class="btn btn-ghost" style="padding: 5px 10px;">Proses</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center t-muted">Belum ada riwayat aktivitas servis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
