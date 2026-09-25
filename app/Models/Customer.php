<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    /**
     * Jejak audit pelanggan ini (relasi polymorphic dari ActivityLog).
     *
     * @return MorphMany<ActivityLog, $this>
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest('id');
    }
}
