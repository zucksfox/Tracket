<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Servis {{ $order->service_code }} | Tracket</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @include('layouts.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">
    <header class="masthead no-print">
        <div class="max-w-3xl w-full mx-auto px-4 flex items-center justify-between h-[54px]">
            <a href="{{ route('tracking.index') }}" class="text-[13px] t-muted hover:underline" style="color: var(--act);"><img src="/brand.svg" alt="Tracket" width="150" height="30"></a>
            <div class="mono text-[13px] font-bold t-ink">{{ $order->service_code }}</div>
        </div>
    </header>

    <main class="max-w-3xl w-full mx-auto px-4 my-6 space-y-6">
        @php $cur = $order->status_meta['step']; @endphp

        <div class="panel p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4" style="border-bottom: 1px solid var(--line-soft);">
                <div>
                    <span class="text-[13px] t-muted block">Unit Servis Pelanggan</span>
                    <h1 class="text-2xl sm:text-[24px] font-bold t-ink leading-tight">{{ $order->device_name }}</h1>
                    <span class="text-[13px] t-muted">Pemilik: <strong class="t-body">{{ $order->customer->name }}</strong></span>
                </div>
                <div class="text-left sm:text-right">
                    <span class="stamp {{ $order->status_meta['bg'] }}">{{ $order->status_meta['label'] }}</span>
                    <div class="text-[13px] t-muted mt-1.5">Diterima: {{ $order->created_at->isoFormat('D MMMM Y - HH:mm') }} WIB</div>
                </div>
            </div>

            @if($order->status === 'ready')
                <div class="banner banner-act">
                    <div class="text-[13px] font-bold mb-0.5">Kabar Baik! Perangkat Anda Telah Selesai Diperbaiki</div>
                    <p class="text-[13px] mt-0.5" style="color: var(--body);">Unit sudah lulus uji coba teknisi dan siap diambil di meja kasir. Mohon siapkan nomor tanda terima atau bukti WhatsApp ini.</p>
                </div>
            @endif

            @if($order->status === 'completed')
                <div class="banner banner-warranty flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <div class="text-[13px] font-bold flex items-center gap-1.5">
                            <span aria-hidden="true">&#9733;</span>
                            <span>{{ $order->warranty_info['label'] }}</span>
                        </div>
                        <p class="text-[13px] mt-0.5" style="color: var(--body);">Jika terjadi kendala pada komponen yang sama selama masa garansi, bawa kembali unit Anda tanpa biaya tambahan.</p>
                    </div>
                    @if($order->warranty_info['is_active'])
                        <div class="shrink-0 text-left sm:text-right">
                            <div class="text-2xl font-semibold tabular-nums">{{ $order->warranty_info['days_remaining'] }} hari</div>
                            <div class="text-[13px]">sisa garansi, s.d. {{ $order->warranty_expires_at ? $order->warranty_expires_at->format('d/m/Y') : '-' }}</div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="space-y-4">
                <h2 class="text-[13px] font-bold t-ink">Tahapan Pengerjaan</h2>
                <div class="timeline">
                    <div class="tstep {{ $cur >= 1 ? 'done' : '' }} {{ $cur === 1 ? 'now' : '' }}">
                        <span class="tdot" aria-hidden="true"></span>
                        <div class="tlabel">1. Unit Diterima di Bengkel</div>
                        <p class="tdesc">Unit masuk antrian laboratorium servis dengan nomor nota {{ $order->service_code }}.</p>
                    </div>
                    <div class="tstep {{ $cur >= 2 ? 'done' : '' }} {{ $cur === 2 ? 'now' : '' }}">
                        <span class="tdot" aria-hidden="true"></span>
                        <div class="tlabel">2. Pemeriksaan & Diagnosa Kerusakan</div>
                        <p class="tdesc">Teknisi mengidentifikasi komponen yang bermasalah dan merancang perbaikan.</p>
                    </div>
                    <div class="tstep {{ $cur >= 3 ? 'done' : '' }} {{ $cur === 3 ? 'now' : '' }}">
                        <span class="tdot" aria-hidden="true"></span>
                        <div class="tlabel">3. Tindakan Perbaikan & Penggantian Suku Cadang</div>
                        <p class="tdesc">Pemasangan sparepart dan pengujian fungsi hardware secara berkala.</p>
                    </div>
                    <div class="tstep {{ $cur >= 4 ? 'done' : '' }} {{ $cur === 4 ? 'now' : '' }}">
                        <span class="tdot" aria-hidden="true"></span>
                        <div class="tlabel">4. Perbaikan Selesai (Siap Diambil)</div>
                        <p class="tdesc">Perangkat telah lolos quality check teknisi dan siap diserahkan.</p>
                    </div>
                    <div class="tstep {{ $cur >= 5 ? 'done' : '' }} {{ $cur === 5 ? 'now' : '' }}">
                        <span class="tdot" aria-hidden="true"></span>
                        <div class="tlabel">5. Unit Diserahkan & Garansi Aktif</div>
                        <p class="tdesc">Unit telah diambil oleh pemilik dan proteksi masa garansi toko aktif.</p>
                    </div>
                </div>
            </div>

            <div class="panel p-5 space-y-2" style="background: var(--paper);">
                <span class="text-[13px] font-semibold t-muted block">Keluhan Saat Masuk:</span>
                <p class="text-[13px] t-body italic">"{{ $order->issue_description }}"</p>
                @if($order->technician_notes)
                    <div class="pt-2 mt-2" style="border-top: 1px solid var(--line-soft);">
                        <span class="text-[13px] font-semibold t-ink block">Laporan Hasil Penanganan Teknisi:</span>
                        <p class="text-[13px] t-body mt-0.5">{{ $order->technician_notes }}</p>
                    </div>
                @endif
            </div>

            @if($order->orderParts->count() > 0)
                <div class="space-y-3">
                    <h2 class="text-[13px] font-bold t-ink">Komponen Yang Diganti</h2>
                    <div class="panel overflow-hidden p-5">
                        @foreach($order->orderParts as $p)
                            <div class="p-5 flex flex-wrap justify-between items-center gap-3" style="border-bottom: 1px solid var(--line-soft); background: var(--paper);">
                                <div>
                                    <div class="text-[13px] font-semibold t-body">{{ $p->sparepart->name }}</div>
                                    <span class="tabular-nums text-[13px] t-muted">{{ $p->quantity }} unit &times; Rp {{ number_format($p->unit_price, 0, ',', '.') }}</span>
                                </div>
                                <div class="tabular-nums text-[13px] t-body">Rp {{ number_format($p->subtotal, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3" style="border-top: 1px solid var(--line-soft);">
                <div>
                    <span class="text-[13px] t-muted block">Total Tagihan Servis:</span>
                    <span class="text-[13px] t-muted">Termasuk jasa perbaikan dan komponen</span>
                </div>
                <div>
                    <span class="tabular-nums font-bold t-ink text-[19px]">Rp {{ number_format($order->total_cost, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="text-center no-print">
            <a href="https://wa.me/{{ config('workshop.wa_number') }}?text=Halo%20Tracket,%20saya%20ingin%20menanyakan%20servis%20nomor%20{{ $order->service_code }}" target="_blank" class="btn btn-ghost">
                Butuh Bantuan? Hubungi WhatsApp Bengkel
            </a>
        </div>
    </main>

    <footer class="max-w-3xl w-full mx-auto px-4 py-4 text-center text-[13px] t-muted" style="border-top: 1px solid var(--line-soft);">
        Tracket &copy; {{ date('Y') }}
    </footer>
</body>
</html>
