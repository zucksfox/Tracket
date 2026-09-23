<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Tracket</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    @include('layouts.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="masthead no-print">
        <div class="masthead-inner max-w-[1180px] mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-[60px] gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 shrink-0">
                    <span class="brand-mark">ST</span>
                    <span class="leading-tight">
                        <span class="block font-semibold text-[15px] t-ink">Tracket</span>
                        <span class="mono block text-[11px] t-muted">Bengkel Servis & Garansi</span>
                    </span>
                </a>

                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="navlink {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('services.create') }}" class="navlink {{ request()->routeIs('services.create') ? 'active' : '' }}">Check-In Servis</a>
                    @endif
                    <a href="{{ route('services.index') }}" class="navlink {{ request()->routeIs('services.index') || request()->routeIs('services.show') ? 'active' : '' }}">Daftar Servis</a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('customers.index') }}" class="navlink {{ request()->routeIs('customers.*') ? 'active' : '' }}">Pelanggan</a>
                    @endif
                    <a href="{{ route('spareparts.index') }}" class="navlink {{ request()->routeIs('spareparts.*') ? 'active' : '' }}">Suku Cadang</a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('technicians.index') }}" class="navlink {{ request()->routeIs('technicians.*') ? 'active' : '' }}">Teknisi</a>
                    @endif
                    <a href="{{ route('tracking.index') }}" target="_blank" class="navlink t-ink" style="border-color: var(--line);">Portal Pelanggan</a>
                </nav>

                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block leading-tight">
                        <div class="text-[12.5px] font-semibold t-body">{{ auth()->user()->name }}</div>
                        <div class="text-[11px]">
                            @if(auth()->user()->isAdmin())
                                <span class="t-ink font-semibold">Administrator</span>
                            @else
                                <span style="color: var(--act-deep); font-weight:600;">Teknisi Servis</span>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar" class="btn btn-ghost" style="padding: 6px 10px;">Keluar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="md:hidden flex overflow-x-auto px-4 py-2 gap-2 border-t" style="border-color: var(--line-soft); background: var(--paper);">
            <a href="{{ route('dashboard') }}" class="navlink {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('services.create') }}" class="navlink {{ request()->routeIs('services.create') ? 'active' : '' }}">Check-In</a>
            @endif
            <a href="{{ route('services.index') }}" class="navlink {{ request()->routeIs('services.index') ? 'active' : '' }}">Servis</a>
            <a href="{{ route('spareparts.index') }}" class="navlink {{ request()->routeIs('spareparts.*') ? 'active' : '' }}">Suku Cadang</a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('technicians.index') }}" class="navlink {{ request()->routeIs('technicians.*') ? 'active' : '' }}">Teknisi</a>
            @endif
            <a href="{{ route('tracking.index') }}" target="_blank" class="navlink t-ink">Tracking</a>
        </div>
    </header>

    <main class="max-w-[1180px] w-full mx-auto px-4 sm:px-6 py-6">
        @if(session('success'))
            <div class="no-print banner banner-act mb-5" role="status">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="no-print banner banner-rose mb-5" role="alert">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="no-print banner banner-act mb-5" role="status">{{ session('info') }}</div>
        @endif
        @if($errors->any())
            <div class="no-print banner banner-rose mb-5" role="alert">
                <div class="font-semibold mb-1">Harap periksa kembali input formulir:</div>
                <ul class="list-disc list-inside text-[12px] space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="no-print border-t" style="border-color: var(--line-soft);">
        <div class="max-w-[1180px] mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-1.5 text-[11.5px] t-muted">
            <span>Tracket v1.1, Sistem Manajemen Bengkel & Garansi</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
