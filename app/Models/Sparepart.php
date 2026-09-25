<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sparepart extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

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

    /**
     * Jejak audit suku cadang ini (relasi polymorphic dari ActivityLog).
     *
     * @return MorphMany<ActivityLog, $this>
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest('id');
    }
}
