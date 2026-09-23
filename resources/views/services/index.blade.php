@extends('layouts.app')

@section('title', 'Daftar Servis')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[20px] font-bold t-ink leading-tight">Daftar Pengerjaan Servis</h1>
            <p class="text-[12.5px] t-muted mt-0.5">Monitoring antrian, proses perbaikan teknisi, dan status penyelesaian unit.</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('services.create') }}" class="btn btn-act">+ Check-In Servis Baru</a>
        @endif
    </div>

    <div class="flex flex-wrap gap-2 pb-3" style="border-bottom: 1px solid var(--line-soft);">
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
            class="btn {{ request('status', 'all') === 'all' ? 'btn-ink' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Semua Servis ({{ $counts['all'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}"
            class="btn {{ request('status') === 'pending' ? 'btn-ink' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Menunggu Diagnosa ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'diagnosing'])) }}"
            class="btn {{ request('status') === 'diagnosing' ? 'btn-ink' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Diagnosa ({{ $counts['diagnosing'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'in_progress'])) }}"
            class="btn {{ request('status') === 'in_progress' ? 'btn-ink' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Pengerjaan ({{ $counts['in_progress'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'ready'])) }}"
            class="btn {{ request('status') === 'ready' ? 'btn-ink' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Siap Diambil ({{ $counts['ready'] }})
        </a>
        <a href="{{ route('services.index', array_merge(request()->except(['status', 'page']), ['status' => 'completed'])) }}"
            class="btn {{ request('status') === 'completed' ? 'btn-ink' : 'btn-ghost' }}" style="padding: 6px 11px;">
            Selesai ({{ $counts['completed'] }})
        </a>
    </div>

    <div class="panel p-4">
        <form action="{{ route('services.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" class="field"
                    placeholder="Cari kode servis (SRV-xxx), nama pelanggan, tipe perangkat, atau no HP...">
            </div>
            @if(auth()->user()->isTechnician())
                <div class="flex items-center">
                    <label class="flex items-center gap-2 text-[12.5px] t-body cursor-pointer select-none">
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

    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
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
                                <div class="t-[11px] t-muted mono">{{ $srv->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="font-semibold t-body">{{ $srv->customer->name }}</div>
                                <div class="mono t-[11px] t-muted">{{ $srv->customer->phone }}</div>
                            </td>
                            <td class="max-w-xs">
                                <div class="font-medium truncate">{{ $srv->device_name }}</div>
                                <div class="t-[11px] t-muted truncate">{{ $srv->issue_description }}</div>
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
                                    <div class="mono t-[10px] mt-1" style="color: var(--warranty);">Garansi: {{ $srv->warranty_info['days_remaining'] }} hr</div>
                                @endif
                            </td>
                            <td class="text-right mono font-semibold t-body">Rp {{ number_format($srv->total_cost, 0, ',', '.') }}</td>
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
