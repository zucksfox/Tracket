<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur & Kartu Garansi | {{ $serviceOrder->service_code }}</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    @include('layouts.theme')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: white; color: black; font-size: 11pt; }
            .nota-sheet { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="p-4 sm:p-8">
    <div class="max-w-3xl mx-auto mb-4 no-print flex justify-between items-center">
        <a href="{{ route('services.show', $serviceOrder) }}" class="text-[12px] hover:underline" style="color: var(--act);">Kembali ke Detail Servis</a>
        <button onclick="window.print()" class="btn btn-ink">Cetak Faktur & Garansi (Ctrl+P)</button>
    </div>

    <div class="nota-sheet max-w-3xl mx-auto panel p-8 sm:p-10" style="background: var(--paper);">
        <div class="flex justify-between items-start pb-4 mb-6" style="border-bottom: 2px solid var(--ink);">
            <div>
                <h1 class="text-[20px] font-bold tracking-tight t-ink uppercase">TRACKET BENGKEL SERVIS</h1>
                <p class="text-[12px] t-muted">Pusat Layanan Servis Smartphone, Laptop & Elektronik</p>
                <p class="text-[12px] t-muted mt-1">{{ config('workshop.address') }} | WhatsApp Customer Service: {{ config('workshop.wa_display') }}</p>
            </div>
            <div class="text-right">
                <div class="text-[11px] font-semibold uppercase t-ink">Faktur & Kartu Garansi</div>
                <div class="mono text-[20px] font-bold tracking-wider t-ink">{{ $serviceOrder->service_code }}</div>
                <div class="text-[12px] t-muted mt-1">Tgl Pengambilan: {{ $serviceOrder->updated_at->isoFormat('D MMMM Y') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6 text-[12.5px]">
            <div class="p-4 rounded-[4px]" style="border: 1px solid var(--line-soft); background: var(--surface);">
                <div class="font-bold t-ink mb-2 pb-1" style="border-bottom: 1px solid var(--line-soft);">Pelanggan</div>
                <div class="space-y-1">
                    <div><span class="t-muted w-20 inline-block">Nama:</span> <strong class="t-body">{{ $serviceOrder->customer->name }}</strong></div>
                    <div><span class="t-muted w-20 inline-block">WhatsApp:</span> <span class="mono t-body">{{ $serviceOrder->customer->phone }}</span></div>
                    <div><span class="t-muted w-20 inline-block">Alamat:</span> <span class="t-body">{{ $serviceOrder->customer->address ?: '-' }}</span></div>
                </div>
            </div>

            <div class="p-4 rounded-[4px]" style="border: 1px solid var(--line-soft); background: var(--surface);">
                <div class="font-bold t-ink mb-2 pb-1" style="border-bottom: 1px solid var(--line-soft);">Perangkat Servis</div>
                <div class="space-y-1">
                    <div><span class="t-muted w-20 inline-block">Unit:</span> <strong class="t-body">{{ $serviceOrder->device_name }}</strong></div>
                    <div><span class="t-muted w-20 inline-block">Serial/IMEI:</span> <span class="mono t-body">{{ $serviceOrder->device_serial ?: '-' }}</span></div>
                    <div><span class="t-muted w-20 inline-block">Teknisi:</span> <span class="t-body">{{ $serviceOrder->technician ? $serviceOrder->technician->name : 'Staff Lab' }}</span></div>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <div class="font-bold t-ink text-[12.5px] mb-2">Rincian Komponen & Jasa Perbaikan:</div>
            <table class="sheet" style="border: 1px solid var(--line-soft);">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Deskripsi Komponen / Jasa</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Harga Satuan</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNo = 1; @endphp
                    @foreach($serviceOrder->orderParts as $item)
                        <tr>
                            <td class="mono t-muted">{{ $rowNo++ }}</td>
                            <td>
                                <div class="t-body">{{ $item->sparepart->name }}</div>
                                <span class="mono text-[10.5px] t-muted">Kode: {{ $item->sparepart->part_code }}</span>
                            </td>
                            <td class="text-center mono">{{ $item->quantity }}</td>
                            <td class="text-right mono">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-right mono font-semibold t-body">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td class="mono t-muted">{{ $rowNo++ }}</td>
                        <td>
                            <div class="t-body">Biaya Jasa Pengerjaan & Analisis Teknis</div>
                            <span class="text-[10.5px] t-muted">Termasuk perakitan ulang & quality control</span>
                        </td>
                        <td class="text-center mono">1</td>
                        <td class="text-right mono">Rp {{ number_format($serviceOrder->labor_cost, 0, ',', '.') }}</td>
                        <td class="text-right mono font-semibold t-body">Rp {{ number_format($serviceOrder->labor_cost, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid var(--ink); background: var(--surface);">
                        <td colspan="4" class="py-2.5 px-3 text-right font-bold uppercase t-ink text-[12px]">Total Pembayaran:</td>
                        <td class="py-2.5 px-3 text-right mono font-bold t-ink text-[15px]">
                            Rp {{ number_format($serviceOrder->total_cost, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mb-6 p-5 text-[12.5px]" style="border: 1.5px solid var(--warranty-line); background: var(--surface); border-radius: 4px;">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 pb-2 mb-3" style="border-bottom: 1px solid var(--warranty-line);">
                <div class="font-bold t-ink uppercase flex items-center gap-2">
                    <span aria-hidden="true" style="color: var(--warranty-line); font-size: 15px;">&#9733;</span>
                    <span>Kartu Jaminan Garansi Resmi Tracket</span>
                </div>
                <div class="mono font-bold text-[11px] px-2.5 py-0.5 self-start" style="border: 1px solid var(--warranty-line); color: var(--warranty); border-radius: 3px; background: var(--paper);">
                    DURASI: {{ $serviceOrder->warranty_days }} HARI
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="t-muted block text-[11px]">Masa Berlaku Garansi:</span>
                    <strong class="t-body mono">
                        {{ $serviceOrder->updated_at->format('d/m/Y') }} s.d {{ $serviceOrder->warranty_expires_at ? $serviceOrder->warranty_expires_at->format('d/m/Y') : '-' }}
                    </strong>
                </div>
                <div>
                    <span class="t-muted block text-[11px]">Status Proteksi:</span>
                    <span class="stamp {{ $serviceOrder->warranty_info['badge_class'] }}" style="transform: none;">{{ $serviceOrder->warranty_info['label'] }}</span>
                </div>
            </div>

            <div class="mt-3 pt-3 text-[10.5px] t-muted space-y-0.5" style="border-top: 1px dashed var(--line-soft);">
                <div class="font-bold t-ink">Ketentuan Klaim Garansi:</div>
                <div>1. Garansi berlaku khusus untuk komponen yang diganti dan keluhan pengerjaan teknis yang sama.</div>
                <div>2. Garansi tidak berlaku jika stiker segel garansi rusak/koyak, unit terkena cairan, jatuh, atau dibongkar pihak ketiga.</div>
                <div>3. Cukup tunjukkan nomor nota ini atau nomor WhatsApp Anda saat klaim di meja kasir.</div>
            </div>
        </div>

        <div class="grid grid-cols-2 text-center text-[12.5px] pt-4">
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
