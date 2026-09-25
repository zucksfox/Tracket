@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<style>
.report-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;margin-bottom:24px}.report-head h1{font-size:26px;font-weight:700;margin:0;color:var(--body)}.report-muted{font-size:13px;line-height:1.6;color:var(--muted)}.report-filter{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}.report-filter label{display:grid;gap:5px;font-size:12px;color:var(--muted)}.report-filter input{padding:9px;border:1px solid var(--line);border-radius:8px;background:var(--surface);color:var(--body);font:inherit}.report-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;margin-bottom:24px}.report-card,.report-panel{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:22px;box-shadow:0 1px 3px #0000000a}.report-card strong{display:block;margin-top:12px;font-size:24px;font-weight:650;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}.report-panel h2{font-size:17px;font-weight:650;margin:0 0 8px}.report-table-scroll{overflow:auto;margin-top:20px}.report-table{width:100%;border-collapse:collapse;min-width:850px;font-size:13px}.report-table th{font-size:12px;color:var(--muted);font-weight:500;text-align:left;background:var(--paper)}.report-table th,.report-table td{padding:13px 12px;border-bottom:1px solid var(--line)}.report-table tbody tr:nth-child(even){background:var(--paper)}.report-table .report-number{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}.report-table a{color:var(--act);text-decoration:underline}.report-code{font-family:ui-monospace,monospace;font-size:12px}.report-date{white-space:nowrap}.report-notice{background:var(--paper);border:1px solid var(--line);padding:14px 18px;border-radius:10px;font-size:12px;color:var(--muted);line-height:1.7;margin-bottom:24px}.report-pagination{margin-top:20px}.report-empty{text-align:center;padding:40px!important;color:var(--muted)}.report-export{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-top:14px}.report-files{margin-top:16px;display:grid;gap:0}.report-file-row{display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap;border-top:1px solid var(--line-soft);padding:9px 0;font-size:12px}.report-file-row a{color:var(--act);text-decoration:underline;font-weight:600}.report-file-meta{color:var(--muted);font-variant-numeric:tabular-nums}@media(max-width:1000px){.report-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:520px){.report-metrics{gap:10px}.report-card,.report-panel{padding:18px}.report-card strong{font-size:20px}.report-filter{width:100%}.report-filter label{flex:1;min-width:0}.report-filter input{width:100%;min-width:0}}
</style>
<div class="report-head"><div><h1>Laporan</h1><p class="report-muted">Pendapatan dari servis selesai, {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}.</p></div>
<form class="report-filter" action="{{ route('reports.index') }}" method="GET"><label for="report-start">Dari<input type="date" id="report-start" name="start" value="{{ $start->toDateString() }}" required></label><label for="report-end">Sampai<input type="date" id="report-end" name="end" value="{{ $end->toDateString() }}" required></label><label for="report-status">Status<select id="report-status" name="status" class="field">@foreach(['completed'=>'Selesai','all'=>'Semua status','pending'=>'Antrian','diagnosing'=>'Diagnosa','in_progress'=>'Pengerjaan','ready'=>'Siap diambil','cancelled'=>'Batal'] as $value=>$label)<option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label><button type="submit" class="btn btn-act">Terapkan</button></form></div>
<div class="report-metrics">
<article class="report-card"><span class="report-muted">Total pendapatan</span><strong>Rp {{ number_format($revenueTotal, 0, ',', '.') }}</strong></article>
<article class="report-card"><span class="report-muted">Biaya Jasa</span><strong>Rp {{ number_format($laborTotal, 0, ',', '.') }}</strong></article>
<article class="report-card"><span class="report-muted">Pendapatan Sparepart</span><strong>Rp {{ number_format($partsTotal, 0, ',', '.') }}</strong></article>
<article class="report-card"><span class="report-muted">Transaksi selesai</span><strong>{{ number_format($transactionCount, 0, ',', '.') }}</strong></article>
</div>
<p class="report-notice">Dasar laporan: servis berstatus selesai, dikelompokkan menurut pembaruan terakhir (updated_at). Pembayaran baru menyimpan metode dan waktu bayar; transaksi lama tanpa bukti tidak otomatis ditandai lunas. Ringkasan hanya menghitung servis selesai, meskipun filter daftar menampilkan status lain. Pendapatan sparepart memakai subtotal yang tercatat pada servis, bukan harga katalog saat ini. Angka ini adalah pendapatan, bukan laba.</p>
@if(abs($revenueTotal - $laborTotal - $partsTotal) > 0.01)<p class="report-notice" role="status">Ada selisih Rp {{ number_format($revenueTotal - $laborTotal - $partsTotal, 0, ',', '.') }} antara total tersimpan dan rincian jasa + sparepart. Periksa nota servis sebelum menggunakan laporan ini.</p>@endif
<section class="report-panel" id="export" aria-labelledby="report-export-title" style="margin-bottom:24px">
<h2 id="report-export-title">Ekspor &amp; Arsip Berkas</h2>
<p class="report-muted">Menyimpan laporan periode dan status yang sedang tampil ke berkas di server, lalu berkas itu dapat diunduh ulang. Isi berkas selalu sama dengan tabel di bawah karena keduanya memakai filter yang sama.</p>
<form class="report-export" action="{{ route('reports.export') }}" method="POST">
@csrf
<input type="hidden" name="start" value="{{ $start->toDateString() }}">
<input type="hidden" name="end" value="{{ $end->toDateString() }}">
<input type="hidden" name="status" value="{{ $status }}">
<label for="report-format">Format berkas<select id="report-format" name="format" class="field" style="min-width:160px">@foreach($exportFormats as $format)<option value="{{ $format }}">{{ strtoupper($format) }}</option>@endforeach</select></label>
<button type="submit" class="btn btn-act">Simpan &amp; ekspor laporan</button>
</form>
@if(count($exportFiles) > 0)
<div class="report-files">
@foreach($exportFiles as $file)
<div class="report-file-row"><span class="report-code">{{ $file['name'] }}</span><span class="report-file-meta">{{ number_format($file['size'] / 1024, 1, ',', '.') }} KB · {{ $file['created_at'] }}</span><a href="{{ route('reports.export.download', ['file' => $file['name']]) }}">Unduh</a></div>
@endforeach
</div>
@else
<p class="report-muted" style="margin-top:12px">Belum ada berkas arsip. Berkas pertama akan muncul di sini setelah ekspor dijalankan.</p>
@endif
</section>
<section class="report-panel" id="transactions" aria-labelledby="report-history"><h2 id="report-history">Riwayat Transaksi</h2><p class="report-muted">{{ $transactions->total() }} servis sesuai filter. Buka nomor nota untuk status, pembayaran dan rincian.</p>
<div class="report-table-scroll" role="region" aria-label="Riwayat transaksi, gulir untuk seluruh kolom" tabindex="0"><table class="report-table"><thead><tr><th scope="col">Pembaruan terakhir</th><th scope="col">Nomor nota</th><th scope="col">Pelanggan / Perangkat</th><th scope="col">Teknisi</th><th scope="col" class="report-number">Jasa</th><th scope="col" class="report-number">Sparepart</th><th scope="col" class="report-number">Total (Rp)</th></tr></thead><tbody>
@forelse($transactions as $order)<tr><td class="report-date">{{ $order->updated_at->format('d M Y H:i') }}</td><td><a class="report-code" href="{{ route('services.show', $order) }}">{{ $order->service_code }}</a></td><td>{{ $order->customer->name }}<br><span class="report-muted">{{ $order->device_name }}</span></td><td>{{ $order->technician?->name ?? 'Belum ditugaskan' }}</td><td class="report-number">{{ number_format($order->labor_cost, 0, ',', '.') }}</td><td class="report-number">{{ number_format($order->order_parts_sum_subtotal ?? 0, 0, ',', '.') }}</td><td class="report-number"><strong>{{ number_format($order->total_cost, 0, ',', '.') }}</strong></td></tr>
@empty<tr><td colspan="7" class="report-empty">Belum ada transaksi selesai pada periode ini. Pilih rentang tanggal lain.</td></tr>@endforelse
</tbody></table></div><div class="report-pagination">{{ $transactions->links() }}</div>
</section>
@endsection
