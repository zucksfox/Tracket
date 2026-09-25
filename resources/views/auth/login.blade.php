<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem | Tracket</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @include('layouts.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-[460px] space-y-6">
            <div class="text-center space-y-2">
            <img src="/brand.svg" alt="Tracket" class="brand-full brand-login" width="240" height="48">
            <p class="text-[13px] t-muted">Sistem Manajemen Servis & Pelacak Garansi Bengkel</p>
        </div>

        @if(session('info'))
            <div class="banner banner-act">{{ session('info') }}</div>
        @endif
        @if(session('error'))
            <div class="banner banner-rose">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="banner banner-rose">{{ $errors->first() }}</div>
        @endif

        <div class="panel p-6 sm:p-8">
            <h2 class="text-xl font-semibold t-ink mb-1">Masuk ke Tracket</h2>
            <p class="text-sm t-muted mb-6">Kelola servis, pelanggan, dan persediaan bengkel.</p>
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block mb-1.5">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="contoh: admin@tracket.test" class="field">
                </div>

                <div>
                    <label for="password" class="block mb-1.5">Kata Sandi</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••" class="field">
                </div>

                <div class="flex items-center justify-between text-[13px]">
                    <label class="flex items-center gap-2 t-muted cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-400" style="accent-color: var(--act);">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-act w-full" style="padding: 10px 14px;">Masuk ke Sistem</button>
            </form>

            <div class="mt-6 pt-5 rule-b" style="border-top: 1px solid var(--line-soft);">
                <div class="text-[13px] t-muted mb-2.5 font-semibold flex items-center justify-between">
                    <span>Coba akun demo</span>
                    <span class="tabular-nums text-[13px] t-ink" style="border: 1px solid var(--line); padding: 1px 6px; border-radius: 8px;">Demo</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('quick-login', 'admin') }}" class="btn btn-ghost flex-col" style="align-items: flex-start; gap: 1px;">
                        <span class="font-semibold">Login Admin</span>
                        <span class="tabular-nums text-[13px] t-muted font-normal">admin@tracket.test</span>
                    </a>
                    <a href="{{ route('quick-login', 'cashier') }}" class="btn btn-ghost flex-col" style="align-items: flex-start; gap: 1px;">
                        <span class="font-semibold">Login Kasir</span>
                        <span class="tabular-nums text-[13px] t-muted font-normal">kasir@tracket.test</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('tracking.index') }}" class="text-[13px] t-muted hover:underline" style="color: var(--act);">Pelanggan? Cek status servis tanpa login di sini</a>
        </div>
    </div>
</body>
</html>
