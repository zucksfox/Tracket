@extends('layouts.app')

@section('title', 'Data Teknisi & Akun Pengguna')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold t-ink tracking-tight">Teknisi & pengguna</h1>
            <p class="text-[13px] t-muted mt-0.5">Kelola akun admin, kasir dan teknisi, beserta beban pengerjaan aktifnya.</p>
        </div>
        <a href="{{ route('technicians.create') }}" class="btn btn-act">Tambah Pengguna</a>
    </div>

    <div class="panel p-5">
        <form action="{{ route('technicians.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input aria-label="Cari pengguna" type="text" name="search" value="{{ request('search') }}" class="field"
                    placeholder="Cari nama atau email pengguna...">
            </div>
            <div>
                <select aria-label="Filter peran" name="role" class="field" onchange="this.form.submit()">
                    <option value="">Semua Peran</option>
                    <option value="cashier" {{ request('role') === 'cashier' ? 'selected' : '' }}>Kasir</option>
                    <option value="technician" {{ request('role') === 'technician' ? 'selected' : '' }}>Teknisi Servis</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-act">Cari</button>
                @if(request('search') || request('role'))
                    <a href="{{ route('technicians.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="panel overflow-hidden p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
            <h2 class="text-base font-semibold t-ink">Daftar pengguna</h2>
            <span class="text-[13px] t-muted">{{ $technicians->total() }} hasil</span>
        </div>
        <div class="overflow-x-auto">
            <table class="sheet">
                <thead>
                    <tr>
                        <th>Nama Pengguna</th>
                        <th>Email Login</th>
                        <th class="text-center">Peran</th>
                        <th class="text-center">Servis Aktif</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($technicians as $user)
                        <tr>
                            <td>
                                <div class="font-semibold t-body">{{ $user->name }}</div>
                                @if($user->id === (int) auth()->id())
                                    <span class="tabular-nums text-[13px]" style="color: var(--warranty);">akun Anda</span>
                                @endif
                            </td>
                            <td class="tabular-nums t-muted">{{ $user->email }}</td>
                            <td class="text-center">
                                @if($user->isAdmin())
                                    <span class="stamp stamp-progress" style="transform: none;">Admin</span>
                                @elseif($user->isCashier())
                                    <span class="stamp stamp-done" style="transform: none;">Kasir</span>
                                @else
                                    <span class="stamp stamp-diagnosing" style="transform: none;">Teknisi</span>
                                @endif
                            </td>
                            <td class="text-center tabular-nums font-semibold t-body">{{ $user->active_services_count }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('technicians.edit', $user) }}" class="btn btn-ghost" style="padding: 6px 10px; font-size: 13px;">Edit</a>
                                    @if($user->id !== (int) auth()->id())
                                    <form action="{{ route('technicians.destroy', $user) }}" method="POST"
                                        onsubmit="return confirm('Hapus akun {{ $user->name }}? Riwayat servis yang pernah ditangani tetap tersimpan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 13px;">Hapus</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center t-muted">Belum ada akun pengguna yang sesuai dengan kriteria pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($technicians->hasPages())
            <div class="p-4" style="border-top: 1px solid var(--line-soft);">
                {{ $technicians->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
