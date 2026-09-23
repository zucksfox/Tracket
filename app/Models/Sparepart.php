<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sparepart extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_code',
        'name',
        'category',
        'stock',
        'buy_price',
        'sell_price',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'buy_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
        ];
    }

    public function isLowStock(): bool
    {
        return $this->stock <= 2;
    }

    public function serviceOrderParts(): HasMany
    {
        return $this->hasMany(ServiceOrderPart::class);
    }
}
