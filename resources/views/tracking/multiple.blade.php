<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Perangkat Servis | Tracket</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @include('layouts.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">
    <header class="masthead no-print">
        <div class="max-w-3xl w-full mx-auto px-4 flex items-center justify-between h-[54px]">
            <a href="{{ route('tracking.index') }}" class="flex items-center gap-2 text-[13px] t-muted">
                <img src="/brand.svg" alt="Tracket" width="150" height="30">
            </a>
        </div>
    </header>

    <main class="max-w-3xl w-full mx-auto px-4 my-8 space-y-5">
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-bold t-ink">Pilih Perangkat yang Ingin Dilacak</h1>
            <p class="text-[13px] t-muted">Nomor WhatsApp ini terdaftar pada beberapa unit servis.</p>
        </div>

        <div class="space-y-3">
            @foreach($orders as $srv)
                <a href="{{ route('tracking.show', $srv->service_code) }}" class="panel block p-5 transition-colors" style="--hover-border: var(--act);"
                    onmouseover="this.style.borderColor='var(--act)'" onmouseout="this.style.borderColor='var(--line)'">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="code-chip">{{ $srv->service_code }}</span>
                            <div class="font-semibold text-[15px] t-ink mt-0.5">{{ $srv->device_name }}</div>
                            <p class="text-[13px] t-muted mt-1 italic">"{{ $srv->issue_description }}"</p>
                        </div>
                        <span class="stamp {{ $srv->status_meta['bg'] }} shrink-0">{{ $srv->status_meta['label'] }}</span>
                    </div>
                    <div class="mt-3 pt-3 flex items-center justify-between text-[13px] t-muted" style="border-top: 1px solid var(--line-soft);">
                        <span>Masuk: {{ $srv->created_at->format('d M Y') }}</span>
                        <span class="font-semibold" style="color: var(--act);">Buka Status Progres</span>
                    </div>
                </a>
            @endforeach
        </div>
    </main>

    <footer class="max-w-3xl w-full mx-auto px-4 py-4 text-center text-[13px] t-muted" style="border-top: 1px solid var(--line-soft);">
        Tracket &copy; {{ date('Y') }}
    </footer>
</body>
</html>
