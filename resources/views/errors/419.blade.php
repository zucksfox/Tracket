<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesi Berakhir | Tracket</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    @include('layouts.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-[460px] space-y-5">
        <div class="text-center space-y-2">
            <div class="brand-mark mx-auto"><img src="/favicon.svg" alt="Tracket" width="36" height="36"></div>
            <h1 class="text-2xl font-bold t-ink">Sesi Anda Sudah Berakhir</h1>
            <p class="text-[13px] t-muted leading-relaxed">
                Halaman dibiarkan terlalu lama terbuka, sehingga sistem menolak aksi terakhir
                demi keamanan data. <strong class="t-body">Data yang sudah tersimpan sebelumnya tetap aman.</strong>
            </p>
        </div>

        <div class="panel p-5 space-y-3">
            <div class="text-[13px] font-semibold t-ink">Lakukan 3 langkah ini:</div>
            <div class="space-y-2.5 text-[13px]">
                <div class="flex items-start gap-2.5">
                    <span class="tabular-nums text-[13px] font-bold t-ink w-5 h-5 flex items-center justify-center rounded-lg shrink-0" style="border: 1px solid var(--line-soft);">1</span>
                    <span>Salin dulu isi catatan yang sedang Anda tulis (kalau ada), biarkan halaman ini terbuka.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="tabular-nums text-[13px] font-bold t-ink w-5 h-5 flex items-center justify-center rounded-lg shrink-0" style="border: 1px solid var(--line-soft);">2</span>
                    <span>Login ulang, lalu buka kembali servisnya.</span>
                    <a href="{{ route('login') }}" class="btn btn-ink" style="padding: 4px 10px; font-size: 12px;">Login Ulang</a>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="tabular-nums text-[13px] font-bold t-ink w-5 h-5 flex items-center justify-center rounded-lg shrink-0" style="border: 1px solid var(--line-soft);">3</span>
                    <span>Ulangi aksi terakhir (update status / pasang part) dari halaman yang baru.</span>
                </div>
            </div>
        </div>

        <p class="text-center text-[13px] t-muted">Tracket, Sistem Manajemen Bengkel & Garansi</p>
    </div>
</body>
</html>
