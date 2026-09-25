<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris suku cadang yang dipasang pada sebuah servis.
 *
 * Tabel ini menyimpan harga satuan dan subtotal pada saat pemasangan, bukan
 * merujuk harga katalog terkini, supaya nota lama tidak berubah nilainya
 * ketika harga suku cadang dinaikkan di kemudian hari.
 */
class ServiceOrderPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id',
        'sparepart_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }
}
