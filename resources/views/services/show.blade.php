@extends('layouts.app')

@section('title', 'Servis #' . $serviceOrder->service_code)

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <a href="{{ route('services.index') }}" class="text-[12px] hover:underline" style="color: var(--act);">Kembali ke Daftar Servis</a>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-[24px] font-bold t-ink mono tracking-tight">{{ $serviceOrder->service_code }}</h1>
                <span class="stamp {{ $serviceOrder->status_meta['bg'] }}">{{ $serviceOrder->status_meta['label'] }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 no-print">
            <a href="{{ route('services.print-receipt', $serviceOrder) }}" target="_blank" class="btn btn-ink">Cetak Tanda Terima</a>
            @if($serviceOrder->status === 'completed')
                <a href="{{ route('services.print-invoice', $serviceOrder) }}" target="_blank" class="btn btn-act">Cetak Faktur & Garansi</a>
            @endif
            <a href="{{ route('tracking.show', $serviceOrder->service_code) }}" target="_blank" class="btn btn-ghost">Lihat Tracking Publik</a>
        </div>
    </div>

    <div class="panel p-4">
        <div class="grid grid-cols-5 gap-2">
            @php
                $steps = [
                    1 => ['key' => 'pending', 'name' => '1. Antrian'],
                    2 => ['key' => 'diagnosing', 'name' => '2. Diagnosa'],
                    3 => ['key' => 'in_progress', 'name' => '3. Pengerjaan'],
                    4 => ['key' => 'ready', 'name' => '4. Siap Ambil'],
                    5 => ['key' => 'completed', 'name' => '5. Selesai & Garansi'],
                ];
                $currentStep = $serviceOrder->status_meta['step'];
            @endphp

            @foreach($steps as $stepNum => $step)
                <div class="text-center mono text-[10.5px] font-semibold py-1.5 px-1 rounded-[4px] border
                    @if($serviceOrder->status === 'cancelled')
                        t-body
                    @elseif($stepNum < $currentStep)
                        t-ink
                    @elseif($stepNum === $currentStep)
                        stamp-progress
                    @else
                        t-muted
                    @endif"
                    style="border-color: var(--line-soft); @if($stepNum === $currentStep && $serviceOrder->status !== 'cancelled') background: var(--act); color:#fff; border-color: var(--act-deep); @elseif($stepNum < $currentStep && $serviceOrder->status !== 'cancelled') border-color: var(--line); @endif">
                    {{ $step['name'] }}
                </div>
            @endforeach
        </div>

        @if($serviceOrder->next_action && $serviceOrder->status !== 'ready')
            @php
                $stepConfirm = match ($serviceOrder->next_action['target_status']) {
                    'diagnosing' => 'Mulai diagnosa untuk unit ini? Status akan berubah menjadi Sedang Diagnosa.',
                    'in_progress' => 'Mulai pengerjaan unit ini? Pastikan hasil diagnosa sudah tercatat.',
                    'ready' => 'Tandai unit ini Siap Diambil? Pelanggan akan melihat status selesai di portal tracking.',
                    default => 'Lanjutkan proses servis ini?',
                };
            @endphp
            <div class="mt-4 pt-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2" style="border-top: 1px solid var(--line-soft);">
                <div class="text-[12px] t-muted">Langkah operasional selanjutnya untuk unit ini:</div>
                <form action="{{ route('services.update-status', $serviceOrder) }}" method="POST"
                    onsubmit="return confirm('{{ $stepConfirm }}')">
                    @csrf
                    <input type="hidden" name="status" value="{{ $serviceOrder->next_action['target_status'] }}">
                    <button type="submit" class="{{ $serviceOrder->next_action['class'] }}">{{ $serviceOrder->next_action['button_label'] }}</button>
                </form>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="panel p-5 space-y-3">
                <h2 class="text-[13px] font-bold t-ink pb-2" style="border-bottom: 1px solid var(--line-soft);">Informasi Unit & Keluhan Pelanggan</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[12.5px]">
                    <div>
                        <span class="t-muted block">Tipe Perangkat:</span>
                        <strong class="t-body text-[14px]">{{ $serviceOrder->device_name }}</strong>
                    </div>
                    <div>
                        <span class="t-muted block">Nomor Seri / IMEI:</span>
                        <span class="mono t-body">{{ $serviceOrder->device_serial ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="t-muted block">Kelengkapan Dititipkan:</span>
                        <span class="t-body">{{ $serviceOrder->accessories_included ?: 'Hanya Unit' }}</span>
                    </div>
                    <div>
                        <span class="t-muted block">Tanggal Masuk:</span>
                        <span class="t-body">{{ $serviceOrder->created_at->format('d F Y, H:i') }} WIB</span>
                    </div>
                </div>
                <div class="pt-1">
                    <span class="t-muted block text-[12px] mb-1">Gejala Kerusakan:</span>
                    <div class="p-3 text-[12.5px] t-body italic rounded-[4px]" style="background: var(--paper); border: 1px solid var(--line-soft);">
                        "{{ $serviceOrder->issue_description }}"
                    </div>
                </div>
            </div>

            <div class="panel p-5 space-y-4">
                <div class="flex items-center justify-between pb-3" style="border-bottom: 1px solid var(--line-soft);">
                    <div>
                        <h2 class="text-[13px] font-bold t-ink">Suku Cadang Digunakan</h2>
                        <p class="text-[11.5px] t-muted">Komponen yang dialokasikan otomatis memotong inventaris gudang.</p>
                    </div>
                    <div class="mono text-[12.5px] font-semibold t-ink">Total Part: Rp {{ number_format($serviceOrder->orderParts->sum('subtotal'), 0, ',', '.') }}</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="sheet">
                        <thead>
                            <tr>
                                <th>Komponen Suku Cadang</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Tarif Satuan</th>
                                <th class="text-right">Subtotal</th>
                                @if($serviceOrder->status !== 'completed' && $serviceOrder->status !== 'cancelled')
                                <th class="text-right">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceOrder->orderParts as $item)
                                <tr>
                                    <td>
                                        <div class="font-semibold t-body">{{ $item->sparepart->name }}</div>
                                        <div class="mono text-[10.5px]" style="color: var(--warranty);">{{ $item->sparepart->part_code }}</div>
                                    </td>
                                    <td class="text-center mono">{{ $item->quantity }}</td>
                                    <td class="text-right mono t-muted">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-right mono font-semibold t-body">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    @if($serviceOrder->status !== 'completed' && $serviceOrder->status !== 'cancelled')
                                    <td class="text-right">
                                        <form action="{{ route('services.remove-part', [$serviceOrder, $item]) }}" method="POST"
                                            onsubmit="return confirm('Lepas suku cadang ini dan kembalikan stoknya ke gudang?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 11.5px;">Lepas Part</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center t-muted">Belum ada suku cadang yang dipasang pada servis ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($serviceOrder->status !== 'completed' && $serviceOrder->status !== 'cancelled')
                    <div class="pt-3 p-3.5 rounded-[4px]" style="border-top: 1px solid var(--line-soft); background: var(--paper); border: 1px solid var(--line-soft);">
                        <div class="text-[12.5px] font-semibold t-ink mb-2">+ Pasang Suku Cadang Baru ke Unit</div>
                        <form action="{{ route('services.add-part', $serviceOrder) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                            @csrf
                            <div class="sm:col-span-7">
                                <label for="sparepart_id" class="block mb-1">Pilih Part (Tersedia di Gudang)</label>
                                <select name="sparepart_id" id="sparepart_id" required class="field">
                                    <option value="">-- Pilih Suku Cadang --</option>
                                    @foreach($availableSpareparts as $part)
                                        @if($part->stock > 0)
                                        <option value="{{ $part->id }}" data-price="{{ $part->sell_price }}" data-stock="{{ $part->stock }}">
                                            [{{ $part->category }}] {{ $part->name }}, Sisa: {{ $part->stock }} (Rp {{ number_format($part->sell_price, 0, ',', '.') }})
                                        </option>
                                        @else
                                        <option value="" disabled>
                                            [{{ $part->category }}] {{ $part->name }} : STOK HABIS, minta admin restock
                                        </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="part_qty" class="block mb-1">Jumlah</label>
                                <input type="number" id="part_qty" name="quantity" value="1" min="1" required class="field mono">
                            </div>

                            <div class="sm:col-span-3">
                                <button type="submit" class="btn btn-act w-full">Pasang ke Unit</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <div class="panel p-5 space-y-3">
                <h2 class="text-[13px] font-bold t-ink pb-2" style="border-bottom: 1px solid var(--line-soft);">Catatan Teknisi & Diagnosis</h2>

                <form action="{{ route('services.update-status', $serviceOrder) }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="status" value="{{ $serviceOrder->status }}">

                    <div>
                        <label for="technician_notes" class="block mb-1">Laporan Teknis Pengerjaan</label>
                        <textarea id="technician_notes" name="technician_notes" rows="3" class="field"
                            placeholder="Tuliskan catatan perbaikan, nomor serial part yang dipasang, atau instruksi pemakaian untuk pelanggan...">{{ old('technician_notes', $serviceOrder->technician_notes) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="technician_id_select" class="block mb-1">Teknisi Penanggung Jawab</label>
                            <select id="technician_id_select" name="technician_id" class="field">
                                <option value="">-- Belum Ditugaskan --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ $serviceOrder->technician_id == $tech->id ? 'selected' : '' }}>
                                        {{ $tech->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="labor_cost_input" class="block mb-1">Tarif Jasa Teknisi (Rp)</label>
                            <input type="number" id="labor_cost_input" name="labor_cost" value="{{ (int)$serviceOrder->labor_cost }}" min="0" step="5000" class="field mono">
                        </div>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="submit" class="btn btn-ink">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-5">
            <div class="panel p-5 space-y-3">
                <h2 class="text-[13px] font-bold t-ink pb-2" style="border-bottom: 1px solid var(--line-soft);">Rincian Biaya Servis</h2>
                <div class="space-y-2 text-[12.5px]">
                    <div class="flex justify-between t-muted">
                        <span>Biaya Jasa Teknisi:</span>
                        <span class="mono t-body">Rp {{ number_format($serviceOrder->labor_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between t-muted">
                        <span>Total Suku Cadang:</span>
                        <span class="mono t-body">Rp {{ number_format($serviceOrder->orderParts->sum('subtotal'), 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-2 flex justify-between items-center" style="border-top: 1.5px solid var(--ink);">
                        <span class="font-bold t-ink text-[13.5px]">Total Tagihan:</span>
                        <span class="mono font-bold t-ink text-[16px]">Rp {{ number_format($serviceOrder->total_cost, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="panel p-5 space-y-3">
                <h2 class="text-[13px] font-bold t-ink pb-2" style="border-bottom: 1px solid var(--line-soft);">Data Kontak Pelanggan</h2>
                <div class="space-y-2 text-[12.5px]">
                    <div>
                        <span class="t-muted block">Nama Pelanggan:</span>
                        <strong class="t-body">{{ $serviceOrder->customer->name }}</strong>
                    </div>
                    <div>
                        <span class="t-muted block">WhatsApp:</span>
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $serviceOrder->customer->phone)) }}" target="_blank"
                            class="mono hover:underline" style="color: var(--act);">
                            {{ $serviceOrder->customer->phone }}
                        </a>
                    </div>
                    <div>
                        <span class="t-muted block">Alamat:</span>
                        <span class="t-body">{{ $serviceOrder->customer->address ?: '-' }}</span>
                    </div>
                </div>
            </div>

            @if($serviceOrder->status === 'ready')
                <div class="panel p-5 space-y-4" style="border-top: 2px solid var(--act);">
                    <div class="flex items-center gap-2">
                        <span class="stamp stamp-progress">Siap Diambil</span>
                        <h2 class="text-[13px] font-bold t-ink">Penyerahan Unit & Garansi</h2>
                    </div>
                    <p class="text-[12px] t-muted">Unit telah selesai diperbaiki. Atur durasi garansi dan serahkan ke pemilik unit.</p>

                    <form action="{{ route('services.checkout', $serviceOrder) }}" method="POST" class="space-y-3"
                        onsubmit="return confirm('Konfirmasi unit sudah diambil pelanggan? Faktur dan kartu garansi akan langsung aktif, dan status menjadi Selesai (tidak bisa diubah kembali).')">
                        @csrf

                        <div>
                            <label class="block mb-1.5">Pilih Durasi Garansi Toko:</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="setWarranty(30)" class="btn btn-ghost" style="padding: 6px 8px;">30 Hari</button>
                                <button type="button" onclick="setWarranty(60)" class="btn btn-ghost" style="padding: 6px 8px;">60 Hari</button>
                                <button type="button" onclick="setWarranty(90)" class="btn btn-ghost" style="padding: 6px 8px;">90 Hari</button>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="number" id="warranty_days_input" name="warranty_days" value="30" min="0" required class="field mono" style="width: 96px;">
                                <span class="text-[12px] t-muted">Hari Kalender</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-act w-full">Konfirmasi Unit Diambil Pelanggan</button>
                    </form>
                </div>
            @endif

            @if($serviceOrder->status === 'completed')
                <div class="panel p-5 space-y-3">
                    <h2 class="text-[13px] font-bold t-ink pb-2" style="border-bottom: 1px solid var(--line-soft);">Status Masa Garansi</h2>
                    <div class="space-y-2 text-[12.5px]">
                        <div class="stamp {{ $serviceOrder->warranty_info['badge_class'] }}" style="transform: none; display: block; text-align: center;">
                            {{ $serviceOrder->warranty_info['label'] }}
                        </div>
                        <div class="flex justify-between pt-1 t-muted">
                            <span>Berlaku Hingga:</span>
                            <span class="mono t-body">{{ $serviceOrder->warranty_expires_at ? $serviceOrder->warranty_expires_at->format('d/m/Y') : 'Tanpa Garansi' }}</span>
                        </div>
                        <div class="flex justify-between t-muted">
                            <span>Durasi Diberikan:</span>
                            <span class="mono t-body">{{ $serviceOrder->warranty_days }} Hari</span>
                        </div>
                    </div>
                </div>
            @endif

            @if($serviceOrder->status !== 'completed' && $serviceOrder->status !== 'cancelled')
                <div>
                    <form action="{{ route('services.cancel', $serviceOrder) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin membatalkan servis ini? Semua suku cadang yang telah dipasang akan otomatis dikembalikan ke stok gudang.');">
                        @csrf
                        <button type="submit" class="btn btn-danger w-full">Batalkan Servis (Rollback Stok)</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function setWarranty(days) {
        document.getElementById('warranty_days_input').value = days;
    }
</script>
@endpush
