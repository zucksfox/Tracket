<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Tracket</title>
    <link rel="stylesheet" href="/fonts/fonts.css">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.theme')
    @stack('styles')
</head>
<body class="app-shell" data-user-id="{{ auth()->id() }}" data-status-motion="{{ session('success') ? '1' : '0' }}" data-error-fields='@json($errors->keys())'>
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $criticalNotice = \App\Models\Sparepart::where('stock', '<=', 2)->count();
    $readyNotice = \App\Models\ServiceOrder::where('status', 'ready')->when($user->isTechnician(), fn($q) => $q->where('technician_id', $user->id))->count();
    $groups = [
        'OPERASIONAL' => [
            ['Dashboard', route('dashboard'), 'dashboard', request()->routeIs('dashboard')],
            ['Servis Masuk', route('services.index'), 'service', request()->routeIs('services.*') && !request()->boolean('my_tasks') && !request()->filled('status')],
            ['Antrian Teknisi', route('services.index', $user->isTechnician() ? ['my_tasks' => 1] : ['status' => 'in_progress']), 'clock', request()->routeIs('services.index') && (request()->boolean('my_tasks') || request('status') === 'in_progress')],
        ],
        'INVENTORI' => [
            ['Suku Cadang', route('spareparts.index'), 'parts', request()->routeIs('spareparts.*')],
        ],
    ];
    if ($isAdmin) {
        $groups['OPERASIONAL'][] = ['Pelanggan', route('customers.index'), 'people', request()->routeIs('customers.*')];
        if (Route::has('reports.index')) {
            $groups['KEUANGAN'] = [
                ['Laporan', route('reports.index'), 'report', request()->routeIs('reports.*') && !request()->has('transactions')],
                ['Riwayat Transaksi', route('reports.index', ['transactions' => 1]).'#transactions', 'clock', request()->routeIs('reports.*') && request()->has('transactions')],
            ];
        }
        $groups['SISTEM'] = [['Manajemen Pengguna', route('technicians.index'), 'people', request()->routeIs('technicians.*')]];
    }
@endphp
<a class="skip-link" href="#main-content">Langsung ke konten</a>
<div class="mobile-backdrop no-print" data-nav-backdrop hidden></div>
<aside class="app-sidebar no-print" aria-label="Navigasi utama">
    <div class="sidebar-head"><a href="{{ route('dashboard') }}" class="sidebar-logo" aria-label="Tracket"><img src="/brand.svg" alt="Tracket" width="190" height="38"></a></div>
    <nav class="app-navigation" id="nav-panel" aria-label="Menu aplikasi">
            @foreach($groups as $heading => $links)
                <div class="nav-group-title">{{ $heading }}</div>
                @foreach($links as [$label, $url, $icon, $active])
                <a href="{{ $url }}" class="navlink {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif>@include('layouts.icon', ['name'=>$icon])<span>{{ $label }}</span></a>
                @endforeach
            @endforeach
            <a href="{{ route('tracking.index') }}" class="navlink navlink-portal">@include('layouts.icon', ['name'=>'portal'])<span>Portal pelanggan</span></a>
    </nav>
</aside>
<header class="app-topbar no-print">
    <button type="button" class="icon-button mobile-menu-button" data-nav-toggle aria-controls="nav-panel" aria-expanded="false" aria-label="Buka menu">@include('layouts.icon', ['name'=>'menu'])</button>
    <div class="topbar-title">@yield('title', 'Dashboard')</div>
    <div class="topbar-actions">
        <form action="{{ route('services.index') }}" method="GET" class="topbar-search" role="search"><label for="global-search" class="sr-only">Cari servis atau pelanggan</label><input id="global-search" name="search" type="search" placeholder="Cari servis atau pelanggan" value="{{ request('search') }}"></form>
        <details class="notification-menu">
            <summary class="icon-button notification-trigger" aria-label="Pemberitahuan bengkel" title="Pemberitahuan bengkel">@include('layouts.icon', ['name'=>'bell'])<span class="notification-badge" data-notification-badge hidden>0</span></summary>
            <div class="notification-panel"><h2>Perlu perhatian</h2><p>Ringkasan kondisi bengkel saat ini.</p><div data-notification-list><a href="{{ route('spareparts.index', ['filter'=>'critical']) }}">{{ $criticalNotice }} suku cadang stok kritis</a></div><a href="{{ route('services.index', array_filter(['status'=>'ready', 'my_tasks'=>$user->isTechnician() ? 1 : null])) }}">{{ $readyNotice }} servis siap diambil</a></div>
        </details>
        <div class="account-menu">
            <button type="button" class="topbar-user" data-account-toggle aria-haspopup="menu" aria-expanded="false" aria-controls="account-panel">
                <span class="user-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <span class="profile-copy"><span class="profile-name">{{ $user->name }}</span><span class="profile-role">{{ $isAdmin ? 'Administrator' : ($user->isCashier() ? 'Kasir' : 'Teknisi servis') }}</span></span>
                <span class="account-caret" aria-hidden="true">@include('layouts.icon', ['name'=>'chevron'])</span>
            </button>
            <div class="account-panel" id="account-panel" role="menu" hidden>
                <div class="account-panel-head">
                    <span class="user-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    <div class="profile-copy"><div class="profile-name">{{ $user->name }}</div><div class="profile-role">{{ $isAdmin ? 'Administrator' : ($user->isCashier() ? 'Kasir' : 'Teknisi servis') }}</div></div>
                </div>
                <button type="button" class="account-action account-action-danger" role="menuitem" data-logout-open>Keluar akun</button>
            </div>
        </div>
    </div>
</header>
<div class="stock-toast-region" data-stock-toast-region aria-live="polite" aria-atomic="true"></div>
<dialog class="confirm-dialog" id="logout-dialog" aria-labelledby="logout-title">
    <form method="dialog" class="confirm-card">
        <h2 id="logout-title">Keluar akun?</h2>
        <p>Sesi {{ $user->name }} akan ditutup. Masuk lagi kapan saja dengan akun yang sama.</p>
        <div class="confirm-actions">
            <button type="submit" class="btn btn-ink" value="cancel">Batal</button>
            <button type="button" class="btn btn-danger" data-logout-confirm>Ya, keluar</button>
        </div>
    </form>
</dialog>
<form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>@csrf</form>
<main class="app-main" id="main-content" tabindex="-1"><div class="app-content">
    @if(session('success'))<div class="no-print banner banner-act mb-5" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="no-print banner banner-rose mb-5" role="alert">{{ session('error') }}</div>@endif
    @if(session('info'))<div class="no-print banner banner-act mb-5" role="status">{{ session('info') }}</div>@endif
    @if($errors->any())<div class="no-print banner banner-rose mb-5" role="alert"><div class="font-semibold mb-1">Periksa kembali formulir:</div><ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
    <footer class="app-footer no-print">Tracket · Bengkel servis & garansi</footer>
</div></main>
@stack('scripts')
</body>
</html>
