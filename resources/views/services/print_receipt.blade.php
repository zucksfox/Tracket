<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Terima Servis | {{ $serviceOrder->service_code }}</title>
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
        <button onclick="window.print()" class="btn btn-ink">Cetak Tanda Terima (Ctrl+P)</button>
    </div>

    <div class="nota-sheet max-w-3xl mx-auto panel p-5 sm:p-8" style="background: var(--paper);">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-5 pb-4 mb-6" style="border-bottom: 2px solid var(--ink);">
            <div>
                <img src="/brand.svg" alt="Tracket" width="190" height="38" class="print-brand">
                <p class="text-[13px]" style="color: var(--muted);">Pusat Layanan Servis Smartphone, Laptop & Elektronik</p>
                <p class="text-[13px] mt-1" style="color: var(--muted);">{{ config('workshop.address') }} | WhatsApp Customer Service: {{ config('workshop.wa_display') }}</p>
            </div>
            <div class="sm:text-right">
                <div class="text-[13px] font-semibold t-ink">Tanda Terima Masuk Servis</div>
                <div class="mono text-2xl font-bold t-ink">{{ $serviceOrder->service_code }}</div>
                <div class="text-[13px] mt-1" style="color: var(--muted);">{{ $serviceOrder->created_at->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6 text-[13px]">
            <div class="p-4 rounded-xl" style="border: 1px solid var(--line-soft); background: var(--surface);">
                <div class="font-bold t-ink mb-2 pb-1" style="border-bottom: 1px solid var(--line-soft);">Identitas Pemilik Unit</div>
                <div class="space-y-1">
                    <div><span class="t-muted w-24 inline-block">Nama:</span> <strong class="t-body">{{ $serviceOrder->customer->name }}</strong></div>
                    <div><span class="t-muted w-24 inline-block">No. WhatsApp:</span> <span class="tabular-nums t-body">{{ $serviceOrder->customer->phone }}</span></div>
                    <div><span class="t-muted w-24 inline-block">Alamat:</span> <span class="t-body">{{ $serviceOrder->customer->address ?: '-' }}</span></div>
                </div>
            </div>

            <div class="p-4 rounded-xl" style="border: 1px solid var(--line-soft); background: var(--surface);">
                <div class="font-bold t-ink mb-2 pb-1" style="border-bottom: 1px solid var(--line-soft);">Spesifikasi Perangkat</div>
                <div class="space-y-1">
                    <div><span class="t-muted w-24 inline-block">Tipe / Merk:</span> <strong class="t-body">{{ $serviceOrder->device_name }}</strong></div>
                    <div><span class="t-muted w-24 inline-block">No. Seri / IMEI:</span> <span class="mono t-body">{{ $serviceOrder->device_serial ?: '-' }}</span></div>
                    <div><span class="t-muted w-24 inline-block">Kelengkapan:</span> <span class="t-body">{{ $serviceOrder->accessories_included ?: 'Hanya Unit' }}</span></div>
                    <div><span class="t-muted w-24 inline-block">Teknisi:</span> <span class="t-body">{{ $serviceOrder->technician ? $serviceOrder->technician->name : 'Antrian Umum' }}</span></div>
                </div>
            </div>
        </div>

        <div class="mb-6 p-4 text-[13px]" style="border: 1px solid var(--line-soft); background: var(--surface); border-radius: 12px;">
            <div class="font-bold t-ink mb-1.5">Keluhan & Gejala Kerusakan:</div>
            <div class="t-body italic p-3 rounded-xl" style="background: var(--paper); border: 1px solid var(--line-soft);">
                "{{ $serviceOrder->issue_description }}"
            </div>
        </div>

        <div class="flex justify-between items-center p-4 mb-6 text-[13px]" style="border: 1px solid var(--line); background: var(--surface); border-radius: 12px;">
            <div>
                <span class="font-semibold t-ink">Estimasi Awal Biaya Jasa:</span>
                <span class="block text-[13px] t-muted">*Biaya akhir akan dikonfirmasikan setelah diagnosa suku cadang selesai.</span>
            </div>
            <div>
                <span class="tabular-nums font-bold t-ink text-[16px]">Rp {{ number_format($serviceOrder->labor_cost, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="p-3 text-[13px] mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2" style="border: 1px solid var(--line); background: var(--surface); border-radius: 12px;">
            <div>
                <strong class="t-ink">Lacak Progres Servis Secara Online:</strong>
                <p class="text-[13px] t-muted mt-0.5">Buka link tracking dan ketik nomor tanda terima di atas atau nomor WhatsApp Anda.</p>
            </div>
            <div class="tabular-nums font-semibold t-ink px-3 py-1.5 text-[13px]" style="border: 1px solid var(--line); border-radius: 8px; background: var(--paper);">
                {{ url('/track') }}
            </div>
        </div>

        <div class="pt-4 mb-8 text-[13px] t-muted space-y-1" style="border-top: 1px solid var(--line-soft);">
            <div class="font-bold t-ink">Ketentuan Layanan:</div>
            <ol class="list-decimal list-inside space-y-0.5">
                <li>Pengambilan unit wajib menyertakan lembar tanda terima ini atau verifikasi nomor WhatsApp terdaftar.</li>
                <li>Pihak bengkel tidak bertanggung jawab atas kehilangan data pada memori/storage. Pelanggan disarankan telah mencadangkan data.</li>
                <li>Barang servis yang tidak diambil lebih dari 60 hari terhitung sejak pemberitahuan selesai berada di luar tanggung jawab bengkel.</li>
            </ol>
        </div>

        <div class="grid grid-cols-2 text-center text-[13px] pt-4">
            <div>
                <div class="t-muted mb-16">Pelanggan Yang Menyerahkan,</div>
                <div class="font-bold t-body">({{ $serviceOrder->customer->name }})</div>
            </div>
            <div>
                <div class="t-muted mb-16">Petugas Customer Service,</div>
                <div class="font-bold t-body">({{ auth()->user()->name }})</div>
            </div>
        </div>
    </div>
</body>
</html>
