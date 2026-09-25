@php
$paths = [
'dashboard' => 'M3 3h8v8H3V3zm10 0h8v5h-8V3zm0 7h8v11h-8V10zM3 13h8v8H3v-8z',
'service' => 'M7 2h10v3h4v17H3V5h4V2zm2 3h6V4H9v1zm-3 5v2h12v-2H6zm0 5v2h8v-2H6z',
'people' => 'M9 3a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm8 1a3 3 0 1 1 0 6h-1a6 6 0 0 0 0-6h1zM2 21v-4c0-3 3-4 7-4s7 1 7 4v4H2zm16 0v-4c0-2-1-3-2-4 4 0 6 1 6 4v4h-4z',
'parts' => 'M3 4h18v5H3V4zm1 7h16v11H4V11zm5 2v2h6v-2H9z',
'report' => 'M3 2h2v18h17v2H3V2zm5 10h3v6H8v-6zm5-5h3v11h-3V7zm5-4h3v15h-3V3z',
'clock' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 4h2v5.5l4 2.4-1 1.7-5-3V6z',
'bell' => 'M12 2a2 2 0 0 1 2 2v.3c3 .8 5 3 5 6.7v5l2 2H3l2-2v-5c0-3.7 2-5.9 5-6.7V4a2 2 0 0 1 2-2zm-3 18h6a3 3 0 0 1-6 0z',
'portal' => 'M2 3h20v14h-8v3h4v2H6v-2h4v-3H2V3zm2 2v10h16V5H4z',
'chevron' => 'm14.6 5.6-1.4-1.4L5.4 12l7.8 7.8 1.4-1.4L8.2 12l6.4-6.4z',
'menu' => 'M3 5h18v2H3V5zm0 6h18v2H3v-2zm0 6h18v2H3v-2z',
];
@endphp
<svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="{{ $paths[$name] ?? $paths['service'] }}"/></svg>
