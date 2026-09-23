<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Servis & Garansi | Tracket</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    @include('layouts.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">
    <header class="masthead no-print">
        <div class="max-w-3xl w-full mx-auto px-4 flex items-center justify-between h-[60px]">
            <a href="{{ route('tracking.index') }}" class="flex items-center gap-3">
                <span class="brand-mark">ST</span>
                <span class="leading-tight">
                    <span class="block font-semibold text-[15px] t-ink">Tracket</span>
                    <span class="mono block text-[11px] t-muted">Portal Pelacakan Servis</span>
                </span>
            </a>
            <a href="{{ route('login') }}" class="btn btn-ink">Masuk Petugas</a>
        </div>
    </header>

    <main class="max-w-2xl w-full mx-auto w-full px-4 py-10 space-y-8 my-auto">
        <div class="text-center space-y-2.5">
            <h1 class="text-[24px] sm:text-[28px] font-bold t-ink leading-tight">Pantau Progres Servis & Status Garansi</h1>
            <p class="text-[13.5px] t-muted max-w-lg mx-auto leading-relaxed">
                Ketik Nomor Nota Servis Anda atau Nomor WhatsApp yang didaftarkan saat penyerahan unit di meja servis.
            </p>
        </div>

        @if(isset($errorMessage) || session('error'))
            <div class="banner banner-rose">{{ $errorMessage ?? session('error') }}</div>
        @endif

        <div class="panel p-6 sm:p-8">
            <form action="{{ route('tracking.search') }}" method="GET" class="space-y-4">
                <div>
                    <label for="search-query" class="block mb-2">Nomor Servis atau Nomor WhatsApp</label>
                    <input type="text" id="search-query" name="query" value="{{ $searchedQuery ?? request('query') }}" required autofocus
                        placeholder="Ketik: SRV-202609-0001 atau 081234567890" class="field mono" style="padding: 12px 14px; font-size: 15px;">
                </div>
                <button type="submit" class="btn btn-act w-full" style="padding: 12px 14px;">Lacak Status Sekarang</button>
            </form>

            @if(app()->environment('local'))
            <div class="mt-6 pt-5" style="border-top: 1px solid var(--line-soft);">
                <span class="text-[12px] t-muted block mb-2 font-semibold">Contoh Data Uji (klik untuk coba langsung):</span>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('tracking.search', ['query' => 'SRV-202609-0001']) }}" class="mono text-[11.5px] btn btn-ghost" style="padding: 6px 10px;">SRV-202609-0001, Selesai Bergaransi</a>
                    <a href="{{ route('tracking.search', ['query' => 'SRV-202609-0002']) }}" class="mono text-[11.5px] btn btn-ghost" style="padding: 6px 10px;">SRV-202609-0002, Siap Diambil</a>
                    <a href="{{ route('tracking.search', ['query' => '081234567890']) }}" class="mono text-[11.5px] btn btn-ghost" style="padding: 6px 10px;">No. HP: 081234567890</a>
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[12.5px]">
            <div class="panel p-3.5">
                <div class="font-semibold t-ink mb-0.5">Transparan & Real-Time</div>
                <p class="t-[11.5px] t-muted">Rincian suku cadang dan tindakan teknisi tercatat jelas.</p>
            </div>
            <div class="panel p-3.5">
                <div class="font-semibold t-ink mb-0.5">Jaminan Garansi Pasti</div>
                <p class="t-[11.5px] t-muted">Hitungan hari sisa garansi aktif terpantau otomatis.</p>
            </div>
            <div class="panel p-3.5">
                <div class="font-semibold t-ink mb-0.5">Tanpa Aplikasi Rumit</div>
                <p class="t-[11.5px] t-muted">Bisa dibuka dari browser HP manapun tanpa registrasi.</p>
            </div>
        </div>
    </main>

    <footer class="max-w-3xl w-full mx-auto px-4 py-4 text-center text-[11.5px] t-muted" style="border-top: 1px solid var(--line-soft);">
        Tracket, Sistem Manajemen Bengkel & Garansi
    </footer>
</body>
</html>
