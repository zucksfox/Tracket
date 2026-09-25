@extends('layouts.app')

@section('title', 'Servis #' . $serviceOrder->service_code)

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <a href="{{ route('services.index') }}" class="text-[13px] hover:underline" style="color: var(--act);">Kembali ke Daftar Servis</a>
            <div class="flex flex-wrap items-center gap-3 mt-1">
                <h1 class="text-lg sm:text-2xl font-semibold t-ink mono tracking-tight">{{ $serviceOrder->service_code }}</h1>
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

    <div class="panel p-5">
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
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
                <div class="text-center tabular-nums text-[13px] font-semibold py-1.5 px-1 rounded-xl border @if($serviceOrder->status === 'cancelled') t-body @elseif($stepNum < $currentStep) t-ink @elseif($stepNum === $currentStep) stamp-progress @else t-muted @endif"
                    style="border-color: var(--line-soft); @if($stepNum === $currentStep && $serviceOrder->status !== 'cancelled') background: var(--act); color:#fff; border-color: var(--act-deep); @elseif($stepNum < $currentStep && $serviceOrder->status !== 'cancelled') border-color: var(--line); @endif">
                    {{ $step['name'] }}
                </div>
            @endforeach
        </div>

        @if(auth()->user()->canRepair() && $serviceOrder->next_action && $serviceOrder->status !== 'ready')
            @php
                $stepConfirm = match ($serviceOrder->next_action['target_status']) {
                    'diagnosing' => 'Mulai diagnosa untuk unit ini? Status akan berubah menjadi Sedang Diagnosa.',
                    'in_progress' => 'Mulai pengerjaan unit ini? Pastikan hasil diagnosa sudah tercatat.',
                    'ready' => 'Tandai unit ini Siap Diambil? Pelanggan akan melihat status Siap Diambil di portal tracking.',
                    default => 'Lanjutkan proses servis ini?',
                };
            @endphp
            <div class="mt-4 pt-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2" style="border-top: 1px solid var(--line-soft);">
                <div class="text-[13px] t-muted">Langkah operasional selanjutnya untuk unit ini:</div>
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
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13px]">
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
                    <span class="t-muted block text-[13px] mb-1">Gejala Kerusakan:</span>
                    <div class="p-3 text-[13px] t-body italic rounded-xl" style="background: var(--paper); border: 1px solid var(--line-soft);">
                        "{{ $serviceOrder->issue_description }}"
                    </div>
                </div>
            </div>

            <div class="panel p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3" style="border-bottom: 1px solid var(--line-soft);">
                    <div>
                        <h2 class="text-[13px] font-bold t-ink">Suku Cadang Digunakan</h2>
                        <p class="text-[13px] t-muted">Komponen yang dialokasikan otomatis memotong inventaris gudang.</p>
                    </div>
                    <div class="tabular-nums text-[13px] font-semibold t-ink">Total Part: Rp {{ number_format($serviceOrder->orderParts->sum('subtotal'), 0, ',', '.') }}</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="sheet">
                        <thead>
                            <tr>
                                <th>Komponen Suku Cadang</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Tarif Satuan</th>
                                <th class="text-right">Subtotal</th>
                                @if(auth()->user()->canRepair() && $serviceOrder->status !== 'completed' && $serviceOrder->status !== 'cancelled')
                                <th class="text-right">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceOrder->orderParts as $item)
                                <tr>
                                    <td>
                                        <div class="font-semibold t-body">{{ $item->sparepart->name }}</div>
                                        <div class="tabular-nums text-[13px]" style="color: var(--warranty);">{{ $item->sparepart->part_code }}</div>
                                    </td>
                                    <td class="text-center tabular-nums">{{ $item->quantity }}</td>
                                    <td class="text-right tabular-nums t-muted">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-right tabular-nums font-semibold t-body">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    @if(auth()->user()->canRepair() && $serviceOrder->status !== 'completed' && $serviceOrder->status !== 'cancelled')
                                    <td class="text-right">
                                        <form action="{{ route('services.remove-part', [$serviceOrder, $item]) }}" method="POST"
                                            onsubmit="return confirm('Lepas suku cadang ini dan kembalikan stoknya ke gudang?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 13px;">Lepas Part</button>
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

                @if(auth()->user()->canRepair() && $serviceOrder->status !== 'completed' && $serviceOrder->status !== 'cancelled')
                    <div class="pt-3 p-3.5 rounded-xl" style="border-top: 1px solid var(--line-soft); background: var(--paper); border: 1px solid var(--line-soft);">
                        <div class="text-[13px] font-semibold t-ink mb-2">Pasang suku cadang</div>
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
                                        <option disabled>
                                            [{{ $part->category }}] {{ $part->name }} : Stok habis, minta admin menambah stok
                                        </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="part_qty" class="block mb-1">Jumlah</label>
                                <input type="number" id="part_qty" name="quantity" value="1" min="1" required class="field tabular-nums">
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

                @if(auth()->user()->canRepair() && !in_array($serviceOrder->status, ['completed', 'cancelled']))
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
                            <input type="number" id="labor_cost_input" name="labor_cost" value="{{ (int)$serviceOrder->labor_cost }}" min="0" step="5000" class="field tabular-nums">
                        </div>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="submit" class="btn btn-ink">Simpan Perubahan</button>
                    </div>
                </form>
                @else
                    <p class="text-[13px] t-body">{{ $serviceOrder->technician_notes ?: 'Belum ada catatan teknis.' }}</p>
                @endif
            </div>
        </div>

        <div class="space-y-5">
            <div class="panel p-5 space-y-3">
                <h2 class="text-[13px] font-bold t-ink pb-2" style="border-bottom: 1px solid var(--line-soft);">Rincian Biaya Servis</h2>
                <div class="space-y-2 text-[13px]">
                    <div class="flex justify-between t-muted">
                        <span>Biaya Jasa Teknisi:</span>
                        <span class="tabular-nums t-body">Rp {{ number_format($serviceOrder->labor_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between t-muted">
                        <span>Total Suku Cadang:</span>
                        <span class="tabular-nums t-body">Rp {{ number_format($serviceOrder->orderParts->sum('subtotal'), 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-2 flex justify-between items-center" style="border-top: 1px solid var(--line-soft);">
                        <span class="font-bold t-ink text-sm">Total Tagihan:</span>
                        <span class="tabular-nums font-bold t-ink text-[16px]">Rp {{ number_format($serviceOrder->total_cost, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="panel p-5 space-y-3">
                <h2 class="text-[13px] font-bold t-ink pb-2" style="border-bottom: 1px solid var(--line-soft);">Data Kontak Pelanggan</h2>
                <div class="space-y-2 text-[13px]">
                    <div>
                        <span class="t-muted block">Nama Pelanggan:</span>
                        <strong class="t-body">{{ $serviceOrder->customer->name }}</strong>
                    </div>
                    <div>
                        <span class="t-muted block">WhatsApp:</span>
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $serviceOrder->customer->phone)) }}" target="_blank"
                            class="tabular-nums hover:underline" style="color: var(--act);">
                            {{ $serviceOrder->customer->phone }}
                        </a>
                    </div>
                    <div>
                        <span class="t-muted block">Alamat:</span>
                        <span class="t-body">{{ $serviceOrder->customer->address ?: '-' }}</span>
                    </div>
                </div>
            </div>

                    @if(auth()->user()->canCheckout() && $serviceOrder->status === 'ready')
                <div class="panel p-5 space-y-4" style="border-top: 2px solid var(--act);">
                    <div class="flex items-center gap-2">
                        <span class="stamp stamp-progress">Siap Diambil</span>
                        <h2 class="text-[13px] font-bold t-ink">Penyerahan Unit & Garansi</h2>
                    </div>
                    <p class="text-[13px] t-muted">Unit telah selesai diperbaiki. Konfirmasi pembayaran dan durasi garansi sebelum menyerahkan unit.</p>

                    <form action="{{ route('services.checkout', $serviceOrder) }}" method="POST" class="space-y-3"
                        onsubmit="return confirm('Konfirmasi unit sudah diambil pelanggan dengan garansi ' + document.getElementById('warranty_days_input').value + ' hari? Status menjadi Selesai dan tidak bisa diubah kembali.')">
                        @csrf
                        <div>
                            <label for="payment_method" class="block mb-1">Metode pembayaran</label>
                            <select id="payment_method" name="payment_method" class="field" required>
                                <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>Tunai</option>
                                <option value="qr" {{ old('payment_method') === 'qr' ? 'selected' : '' }}>QR — konfirmasi manual</option>
                            </select>
                            <p class="text-[13px] t-muted mt-2">Tunai: pastikan uang telah diterima. QR: gunakan QR merchant bengkel di luar aplikasi; aplikasi tidak membuat QR pembayaran.</p>
                            <label class="flex items-start gap-2 mt-3 text-[13px]">
                                <input type="checkbox" name="confirm_payment" value="1" {{ old('confirm_payment') ? 'checked' : '' }}>
                                <span>Tandai Sudah Dibayar — saya telah memeriksa transfer masuk (wajib untuk QR).</span>
                            </label>
                        </div>
                        <fieldset>
                            <legend class="block mb-1.5 text-[13px] font-medium">Durasi Garansi Toko</legend>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach([0 => 'Tanpa Garansi', 30 => '30 Hari', 60 => '60 Hari', 90 => '90 Hari'] as $days => $label)
                                <label class="warranty-choice">
                                    <input type="radio" name="warranty_preset" value="{{ $days }}" data-warranty-preset="{{ $days }}" {{ $days === 30 ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <label for="warranty_days_input" class="text-[13px] t-muted">Durasi khusus:</label>
                                <input type="number" id="warranty_days_input" name="warranty_days" value="30" min="0" required class="field tabular-nums" style="width: 110px;">
                                <span class="text-[13px] t-muted">hari</span>
                            </div>
                            <p class="text-[12px] t-muted mt-2">Berlaku sampai: <strong id="warranty-end-preview">{{ now()->addDays(30)->format('d/m/Y') }}</strong></p>
                        </fieldset>
                        <button type="submit" class="btn btn-act w-full">Konfirmasi Lunas & Serahkan Unit</button>
                    </form>
                </div>
            @endif

            @if($serviceOrder->status === 'completed')
                <div class="panel p-5 space-y-3">
                    <h2 class="text-[13px] font-bold t-ink">Pembayaran</h2>
                    <p>{{ $serviceOrder->payment_status === 'paid' ? 'LUNAS' : 'Pembayaran belum tercatat' }}</p>
                    <p>{{ $serviceOrder->payment_method === 'cash' ? 'Tunai' : ($serviceOrder->payment_method === 'qr' ? 'QR (dikonfirmasi manual)' : 'Metode tidak tercatat') }}</p>
                    <p>{{ $serviceOrder->paid_at?->format('d/m/Y H:i') ?? 'Waktu bayar tidak tercatat' }}</p>
                </div>
                <div class="panel p-5 space-y-3">
                    <h2 class="text-[13px] font-bold t-ink pb-2" style="border-bottom: 1px solid var(--line-soft);">Status Masa Garansi</h2>
                    <div class="space-y-2 text-[13px]">
                        <div class="stamp {{ $serviceOrder->warranty_info['badge_class'] }}" style="transform: none; display: block; text-align: center;">
                            {{ $serviceOrder->warranty_info['label'] }}
                        </div>
                        <div class="flex justify-between pt-1 t-muted">
                            <span>Berlaku Hingga:</span>
                            <span class="tabular-nums t-body">{{ $serviceOrder->warranty_expires_at ? $serviceOrder->warranty_expires_at->format('d/m/Y') : 'Tanpa Garansi' }}</span>
                        </div>
                        <div class="flex justify-between t-muted">
                            <span>Durasi Diberikan:</span>
                            <span class="tabular-nums t-body">{{ $serviceOrder->warranty_days }} Hari</span>
                        </div>
                    </div>
                </div>
            @endif

            @if(auth()->user()->isAdmin() && $serviceOrder->status !== 'completed' && $serviceOrder->status !== 'cancelled')
                <div>
                    <form action="{{ route('services.cancel', $serviceOrder) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin membatalkan servis ini? Semua suku cadang yang telah dipasang akan otomatis dikembalikan ke stok gudang.');">
                        @csrf
                        <button type="submit" class="btn btn-danger w-full">Batalkan servis & kembalikan stok</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const warrantyInput = document.getElementById('warranty_days_input');
    const warrantyPreview = document.getElementById('warranty-end-preview');
    document.querySelectorAll('[data-warranty-preset]').forEach(preset => preset.addEventListener('change', () => {
        if (!warrantyInput) return;
        warrantyInput.value = preset.dataset.warrantyPreset;
        warrantyInput.dispatchEvent(new Event('input'));
    }));
    warrantyInput?.addEventListener('input', () => {
        const days = Math.max(0, Number.parseInt(warrantyInput.value || '0', 10));
        const end = new Date();
        end.setDate(end.getDate() + days);
        if (warrantyPreview) warrantyPreview.textContent = end.toLocaleDateString('id-ID');
        document.querySelectorAll('[data-warranty-preset]').forEach(preset => {
            preset.checked = Number(preset.dataset.warrantyPreset) === days;
        });
    });

</script>
@endpush
