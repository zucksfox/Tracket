<?php

/*
|--------------------------------------------------------------------------
| Workshop Contact Configuration
|--------------------------------------------------------------------------
| Kontak bengkel tunggal: dipakai tanda terima, faktur, dan tombol
| WhatsApp di portal pelanggan. Ubah di sini, semua dokumen ikut.
*/

return [
    'name' => env('WORKSHOP_NAME', 'Tracket Bengkel Servis'),
    'address' => env('WORKSHOP_ADDRESS', 'Jl. Kenanga No. 12, Kec. Lowokwaru, Kota Malang'),
    'wa_number' => env('WORKSHOP_WA_NUMBER', '62812345678901'), // format internasional tanpa +
    'wa_display' => env('WORKSHOP_WA_DISPLAY', '0812-3456-7890'),
];
