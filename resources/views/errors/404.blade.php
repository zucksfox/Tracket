<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tidak Ditemukan | Tracket</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    @include('layouts.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-[480px] space-y-5">
        <div class="text-center space-y-2">
            <div class="brand-mark mx-auto"><img src="/favicon.svg" alt="Tracket" width="36" height="36"></div>
            <h1 class="text-2xl font-bold t-ink">Data Servis Tidak Ditemukan</h1>
            <p class="text-[13px] t-muted leading-relaxed">
                Nomor servis yang Anda buka tidak ada di sistem, kemungkinan karena
                data sedang diperbarui atau nomor yang dituju salah.
            </p>
        </div>

        <div class="panel p-5 space-y-3">
            <div class="text-[13px] font-semibold t-ink">Yang bisa Anda lakukan:</div>
            <div class="space-y-2.5 text-[13px]">
                <div class="flex items-start gap-2.5">
                    <span class="tabular-nums text-[13px] font-bold t-ink w-5 h-5 flex items-center justify-center rounded-lg shrink-0" style="border: 1px solid var(--line-soft);">1</span>
                    <span>Buka <a href="{{ url('/services') }}" class="font-semibold hover:underline" style="color: var(--act);">Daftar Servis</a>, cari unit yang dimaksud dari daftar.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="tabular-nums text-[13px] font-bold t-ink w-5 h-5 flex items-center justify-center rounded-lg shrink-0" style="border: 1px solid var(--line-soft);">2</span>
                    <span>Bila tadi membuka dari tab lama, tutup tab itu dan mulai dari Daftar Servis agar tidak salah nomor.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="tabular-nums text-[13px] font-bold t-ink w-5 h-5 flex items-center justify-center rounded-lg shrink-0" style="border: 1px solid var(--line-soft);">3</span>
                    <span>Untuk pelanggan: pastikan nomor nota di kertas tanda terima sudah sesuai format <span class="mono">SRV-YYYYMM-XXXX</span>.</span>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ url('/services') }}" class="btn btn-act">Kembali ke Daftar Servis</a>
        </div>

        <p class="text-center text-[13px] t-muted">Tracket, Sistem Manajemen Bengkel & Garansi</p>
    </div>
</body>
</html>
