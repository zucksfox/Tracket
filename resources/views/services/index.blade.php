@extends('layouts.app')

@section('title', 'Daftar Servis')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold t-ink leading-tight">Daftar servis</h1>
            <p class="text-[13px] t-muted mt-0.5">Pantau antrean, proses perbaikan, dan unit yang siap diambil.</p>
        </div>
        @if(auth()->user()->canCheckout())
        <a href="{{ route('services.create') }}" class="btn btn-act">Tambah servis</a>
        @endif
    </div>

    <div class="panel p-5 flex flex-wrap gap-2" style="border-bottom: 1px solid var(--line-soft);">
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
            class="btn {{ request('status', 'all') === 'all' ? 'btn-act' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Semua Servis ({{ $counts['all'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}"
            class="btn {{ request('status') === 'pending' ? 'btn-act' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Menunggu Diagnosa ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'diagnosing'])) }}"
            class="btn {{ request('status') === 'diagnosing' ? 'btn-act' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Diagnosa ({{ $counts['diagnosing'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'in_progress'])) }}"
            class="btn {{ request('status') === 'in_progress' ? 'btn-act' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Pengerjaan ({{ $counts['in_progress'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'ready'])) }}"
            class="btn {{ request('status') === 'ready' ? 'btn-act' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Siap Diambil ({{ $counts['ready'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'completed'])) }}"
            class="btn {{ request('status') === 'completed' ? 'btn-act' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Selesai ({{ $counts['completed'] }})
        </a>
    </div>

    <div class="panel p-5">
        <form action="{{ route('services.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="flex-1">
                <input aria-label="Cari servis" type="text" name="search" value="{{ request('search') }}" class="field"
                    placeholder="Cari kode servis (SRV-xxx), nama pelanggan, tipe perangkat, atau no HP...">
            </div>
            @if(auth()->user()->isTechnician())
                <div class="flex items-center">
                    <label class="flex items-center gap-2 text-[13px] t-body cursor-pointer select-none">
                        <input type="checkbox" name="my_tasks" value="1" {{ request('my_tasks') ? 'checked' : '' }}
                            onchange="this.form.submit()" style="accent-color: var(--act);">
                        <span>Hanya Tugas Saya</span>
                    </label>
                </div>
            @endif
            <div class="flex gap-2">
                <button type="submit" class="btn btn-act">Cari</button>
                @if(request('search') || request('my_tasks'))
                    <a href="{{ route('services.index', ['status' => request('status', 'all')]) }}" class="btn btn-ghost">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="panel overflow-hidden p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
            <h2 class="text-base font-semibold t-ink">Daftar unit servis</h2>
            <span class="text-[13px] t-muted">{{ $services->total() }} hasil</span>
        </div>
        <div class="mobile-record-list">
            @forelse($services as $srv)
                <a href="{{ route('services.show', $srv) }}" class="mobile-record-card">
                    <div class="mobile-record-head"><span class="code-chip">{{ $srv->service_code }}</span><span class="stamp {{ $srv->status_meta['bg'] }}">{{ $srv->status_meta['label'] }}</span></div>
                    <strong>{{ $srv->customer->name }}</strong><span>{{ $srv->device_name }}</span><span class="mobile-record-muted">{{ $srv->technician?->name ?? 'Belum ditentukan' }} · Rp {{ number_format($srv->total_cost, 0, ',', '.') }}</span>
                </a>
            @empty
                <div class="mobile-record-empty">Tidak ada data pengerjaan servis pada filter ini.</div>
            @endforelse
        </div>
        <div class="overflow-x-auto desktop-record-table">
            <table class="sheet">
                <thead>
                    <tr>
                        <th>No. Servis</th>
                        <th>Pelanggan</th>
                        <th>Perangkat & Keluhan</th>
                        <th>Teknisi</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Total Biaya</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $srv)
                        <tr>
                            <td>
                                <a href="{{ route('services.show', $srv) }}" class="code-chip hover:underline">{{ $srv->service_code }}</a>
                                <div class="text-[13px] t-muted tabular-nums">{{ $srv->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="font-semibold t-body">{{ $srv->customer->name }}</div>
                                <div class="tabular-nums text-[13px] t-muted">{{ $srv->customer->phone }}</div>
                            </td>
                            <td class="max-w-xs">
                                <div class="font-medium truncate">{{ $srv->device_name }}</div>
                                <div class="text-[13px] t-muted truncate">{{ $srv->issue_description }}</div>
                            </td>
                            <td>
                                @if($srv->technician)
                                    <span class="t-body">{{ $srv->technician->name }}</span>
                                @else
                                    <span class="t-muted italic">Belum ditentukan</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="stamp {{ $srv->status_meta['bg'] }}">{{ $srv->status_meta['label'] }}</span>
                                @if($srv->status === 'completed' && $srv->warranty_info['is_active'])
                                    <div class="tabular-nums text-[13px] mt-1" style="color: var(--warranty);">Garansi: {{ $srv->warranty_info['days_remaining'] }} hr</div>
                                @endif
                            </td>
                            <td class="text-right tabular-nums font-semibold t-body">Rp {{ number_format($srv->total_cost, 0, ',', '.') }}</td>
                            <td class="text-right">
                                <a href="{{ route('services.show', $srv) }}" class="btn btn-act" style="padding: 6px 10px;">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center t-muted">Tidak ada data pengerjaan servis pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="p-4" style="border-top: 1px solid var(--line-soft);">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
