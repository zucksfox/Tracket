<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur & Kartu Garansi | {{ $serviceOrder->service_code }}</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    @include('layouts.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .nota-sheet { background: #fff !important; color: #111; overflow-wrap: anywhere; }
        .nota-sheet * { color: #111 !important; border-color: #d1d5db !important; }
        .nota-sheet .stamp { background: #fff !important; border: 1px solid #777; }
        .nota-sheet tr, .nota-sheet .grid > div { break-inside: avoid; }
        .nota-sheet table { width: 100%; }
        @page { size: A4; margin: 12mm; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; color: black; font-size: 11pt; }
            .nota-sheet { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: none !important; }
            .nota-sheet * { background: #fff !important; color: #000 !important; }
            .nota-sheet .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
</head>
<body class="p-4 sm:p-8">
    <div class="max-w-3xl mx-auto mb-4 no-print flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <a href="{{ route('services.show', $serviceOrder) }}" class="text-[13px] hover:underline" style="color: var(--act);">Kembali ke Detail Servis</a>
        <button onclick="window.print()" class="btn btn-ink">Cetak Faktur & Garansi (Ctrl+P)</button>
    </div>

    <div class="nota-sheet max-w-3xl mx-auto panel p-5 sm:p-8" style="background: var(--paper);">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-5 pb-4 mb-6" style="border-bottom: 2px solid var(--ink);">
            <div>
                <img src="/brand.svg" alt="Tracket" width="190" height="38" class="print-brand">
                <p class="text-[13px] t-muted">Pusat Layanan Servis Smartphone, Laptop & Elektronik</p>
                <p class="text-[13px] t-muted mt-1">{{ config('workshop.address') }} | WhatsApp Customer Service: {{ config('workshop.wa_display') }}</p>
            </div>
            <div class="sm:text-right">
                <div class="text-[13px] font-semibold t-ink">Faktur & Kartu Garansi</div>
                <div class="mono text-2xl font-bold t-ink">{{ $serviceOrder->service_code }}</div>
                <div class="text-[13px] t-muted mt-1">Tgl Pengambilan: {{ $serviceOrder->updated_at->isoFormat('D MMMM Y') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6 text-[13px]">
            <div class="p-4 rounded-xl" style="border: 1px solid var(--line-soft); background: var(--surface);">
                <div class="font-bold t-ink mb-2 pb-1" style="border-bottom: 1px solid var(--line-soft);">Pelanggan</div>
                <div class="space-y-1">
                    <div><span class="t-muted w-20 inline-block">Nama:</span> <strong class="t-body">{{ $serviceOrder->customer->name }}</strong></div>
                    <div><span class="t-muted w-20 inline-block">WhatsApp:</span> <span class="tabular-nums t-body">{{ $serviceOrder->customer->phone }}</span></div>
                    <div><span class="t-muted w-20 inline-block">Alamat:</span> <span class="t-body">{{ $serviceOrder->customer->address ?: '-' }}</span></div>
                </div>
            </div>

            <div class="p-4 rounded-xl" style="border: 1px solid var(--line-soft); background: var(--surface);">
                <div class="font-bold t-ink mb-2 pb-1" style="border-bottom: 1px solid var(--line-soft);">Perangkat Servis</div>
                <div class="space-y-1">
                    <div><span class="t-muted w-20 inline-block">Unit:</span> <strong class="t-body">{{ $serviceOrder->device_name }}</strong></div>
                    <div><span class="t-muted w-20 inline-block">Serial/IMEI:</span> <span class="mono t-body">{{ $serviceOrder->device_serial ?: '-' }}</span></div>
                    <div><span class="t-muted w-20 inline-block">Teknisi:</span> <span class="t-body">{{ $serviceOrder->technician ? $serviceOrder->technician->name : 'Staff Lab' }}</span></div>
                </div>
            </div>
        </div>

        <div class="mb-6 text-[13px]">
            <strong>{{ $serviceOrder->payment_status === 'paid' ? 'LUNAS' : 'Pembayaran belum tercatat' }}</strong>
            <p>Metode: {{ $serviceOrder->payment_method === 'cash' ? 'Tunai' : ($serviceOrder->payment_method === 'qr' ? 'QR (konfirmasi manual)' : 'Tidak tercatat') }}</p>
            <p>Waktu bayar: {{ $serviceOrder->paid_at?->format('d/m/Y H:i') ?? 'Tidak tercatat' }}</p>
        </div>
        <div class="mb-6">
            <div class="font-bold t-ink text-[13px] mb-2">Rincian Komponen & Jasa Perbaikan:</div>
            <div class="overflow-x-auto" role="region" aria-label="Rincian faktur, gulir untuk melihat semua kolom" tabindex="0">
            <table class="sheet" style="border: 1px solid var(--line-soft);">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Deskripsi Komponen / Jasa</th>
                        <th class="text-center">Qty</th>
                        <th class="sm:text-right">Harga Satuan</th>
                        <th class="sm:text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNo = 1; @endphp
                    @foreach($serviceOrder->orderParts as $item)
                        <tr>
                            <td class="tabular-nums t-muted">{{ $rowNo++ }}</td>
                            <td>
                                <div class="t-body">{{ $item->sparepart->name }}</div>
                                <span class="tabular-nums text-[13px] t-muted">Kode: {{ $item->sparepart->part_code }}</span>
                            </td>
                            <td class="text-center tabular-nums">{{ $item->quantity }}</td>
                            <td class="text-right tabular-nums">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-right tabular-nums font-semibold t-body">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td class="tabular-nums t-muted">{{ $rowNo++ }}</td>
                        <td>
                            <div class="t-body">Biaya Jasa Pengerjaan & Analisis Teknis</div>
                            <span class="text-[13px] t-muted">Termasuk perakitan ulang & quality control</span>
                        </td>
                        <td class="text-center tabular-nums">1</td>
                        <td class="text-right tabular-nums">Rp {{ number_format($serviceOrder->labor_cost, 0, ',', '.') }}</td>
                        <td class="text-right tabular-nums font-semibold t-body">Rp {{ number_format($serviceOrder->labor_cost, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid var(--ink); background: var(--surface);">
                        <td colspan="4" class="py-2.5 px-3 text-right font-bold t-ink text-[13px]">Total Pembayaran:</td>
                        <td class="py-2.5 px-3 text-right tabular-nums font-bold t-ink text-[15px]">
                            Rp {{ number_format($serviceOrder->total_cost, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>

        <div class="mb-6 p-5 text-[13px]" style="border: 1.5px solid var(--warranty-line); background: var(--surface); border-radius: 12px;">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 pb-2 mb-3" style="border-bottom: 1px solid var(--warranty-line);">
                <div class="font-bold t-ink flex items-center gap-2">
                    <span aria-hidden="true" style="color: var(--warranty-line); font-size: 15px;">&#9733;</span>
                    <span>Kartu Jaminan Garansi Resmi Tracket</span>
                </div>
                <div class="tabular-nums font-bold text-[13px] px-2.5 py-0.5 self-start" style="border: 1px solid var(--warranty-line); color: var(--warranty); border-radius: 8px; background: var(--paper);">
                    Durasi: {{ $serviceOrder->warranty_days }} hari
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="t-muted block text-[13px]">Masa Berlaku Garansi:</span>
                    <strong class="t-body tabular-nums">
                        {{ $serviceOrder->updated_at->format('d/m/Y') }} s.d {{ $serviceOrder->warranty_expires_at ? $serviceOrder->warranty_expires_at->format('d/m/Y') : '-' }}
                    </strong>
                </div>
                <div>
                    <span class="t-muted block text-[13px]">Status Proteksi:</span>
                    <span class="stamp {{ $serviceOrder->warranty_info['badge_class'] }}" style="transform: none;">{{ $serviceOrder->warranty_info['label'] }}</span>
                </div>
            </div>

            <div class="mt-3 pt-3 text-[13px] t-muted space-y-0.5" style="border-top: 1px solid var(--line-soft);">
                <div class="font-bold t-ink">Ketentuan Klaim Garansi:</div>
                <div>1. Garansi berlaku khusus untuk komponen yang diganti dan keluhan pengerjaan teknis yang sama.</div>
                <div>2. Garansi tidak berlaku jika stiker segel garansi rusak/koyak, unit terkena cairan, jatuh, atau dibongkar pihak ketiga.</div>
                <div>3. Cukup tunjukkan nomor nota ini atau nomor WhatsApp Anda saat klaim di meja kasir.</div>
            </div>
        </div>

        <div class="grid grid-cols-2 text-center text-[13px] pt-4">
            <div>
                <div class="t-muted mb-16">Pelanggan Yang Menerima Unit,</div>
                <div class="font-bold t-body">({{ $serviceOrder->customer->name }})</div>
            </div>
            <div>
                <div class="t-muted mb-16">Kasir / Admin Penanggung Jawab,</div>
                <div class="font-bold t-body">({{ auth()->user()->name }})</div>
            </div>
        </div>
    </div>
</body>
</html>
