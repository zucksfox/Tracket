<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ServiceOrder extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'service_code',
        'customer_id',
        'technician_id',
        'device_name',
        'device_serial',
        'issue_description',
        'accessories_included',
        'status',
        'labor_cost',
        'total_cost',
        'payment_status',
        'payment_method',
        'paid_at',
        'warranty_days',
        'warranty_expires_at',
        'technician_notes',
    ];

    protected function casts(): array
    {
        return [
            'labor_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'warranty_days' => 'integer',
            'paid_at' => 'datetime',
            'warranty_expires_at' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function orderParts(): HasMany
    {
        return $this->hasMany(ServiceOrderPart::class);
    }

    /**
     * Jejak audit servis ini (relasi polymorphic dari ActivityLog).
     *
     * @return MorphMany<ActivityLog, $this>
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest('id');
    }

    /**
     * Auto-Generate sequential unique service code: SRV-YYYYMM-XXXX
     * Wrapped in lockForUpdate to prevent race conditions during concurrent check-ins.
     */
    public static function generateServiceCode(): string
    {
        return DB::transaction(function () {
            $prefix = 'SRV-'.date('Ym').'-';

            // Lock recent records to determine max sequential number safely
            $latest = self::where('service_code', 'LIKE', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('service_code', 'desc')
                ->first();

            if ($latest) {
                $lastNumber = (int) substr($latest->service_code, -4);
                $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
            } else {
                $nextNumber = '0001';
            }

            return $prefix.$nextNumber;
        });
    }

    /**
     * Recalculate and persist total cost (labor_cost + sum of parts subtotal)
     */
    public function recalculateTotal(): void
    {
        $partsTotal = $this->orderParts()->sum('subtotal');
        $this->total_cost = (float) $this->labor_cost + (float) $partsTotal;
        $this->save();
    }

    /**
     * Status metadata with honest Indonesian copy and badge colors
     */
    public function getStatusMetaAttribute(): array
    {
        return match ($this->status) {
            'pending' => [
                'label' => 'Menunggu Diagnosa',
                'bg' => 'stamp-pending',
                'text' => '',
                'border' => '',
                'step' => 1,
            ],
            'diagnosing' => [
                'label' => 'Sedang Diagnosa',
                'bg' => 'stamp-diagnosing',
                'text' => '',
                'border' => '',
                'step' => 2,
            ],
            'in_progress' => [
                'label' => 'Sedang Dikerjakan',
                'bg' => 'stamp-progress',
                'text' => '',
                'border' => '',
                'step' => 3,
            ],
            'ready' => [
                'label' => 'Siap Diambil',
                'bg' => 'stamp-ready',
                'text' => '',
                'border' => '',
                'step' => 4,
            ],
            'completed' => [
                'label' => 'Selesai & Diambil',
                'bg' => 'stamp-done',
                'text' => '',
                'border' => '',
                'step' => 5,
            ],
            'cancelled' => [
                'label' => 'Dibatalkan',
                'bg' => 'stamp-cancelled',
                'text' => '',
                'border' => '',
                'step' => 0,
            ],
            default => [
                'label' => ucfirst($this->status),
                'bg' => 'stamp-diagnosing',
                'text' => '',
                'border' => '',
                'step' => 0,
            ],
        };
    }

    /**
     * Single action progression button helper
     */
    public function getNextActionAttribute(): ?array
    {
        return match ($this->status) {
            'pending' => [
                'target_status' => 'diagnosing',
                'button_label' => 'Mulai Diagnosa',
                'class' => 'btn btn-act',
            ],
            'diagnosing' => [
                'target_status' => 'in_progress',
                'button_label' => 'Mulai Pengerjaan',
                'class' => 'btn btn-act',
            ],
            'in_progress' => [
                'target_status' => 'ready',
                'button_label' => 'Tandai Siap Diambil',
                'class' => 'btn btn-act',
            ],
            'ready' => [
                'target_status' => 'completed',
                'button_label' => 'Serahkan ke Pelanggan (Checkout)',
                'class' => 'btn btn-ink',
            ],
            default => null,
        };
    }

    /**
     * Real-time warranty remaining calculator
     */
    public function getWarrantyInfoAttribute(): array
    {
        if ($this->status !== 'completed' || ! $this->warranty_expires_at) {
            return [
                'has_warranty' => false,
                'is_active' => false,
                'days_remaining' => 0,
                'label' => 'Tanpa Garansi',
                'badge_class' => 'stamp-diagnosing',
            ];
        }

        $today = Carbon::today();
        $expiry = Carbon::parse($this->warranty_expires_at)->startOfDay();
        $daysRemaining = (int) $today->diffInDays($expiry, false);

        if ($daysRemaining >= 0) {
            return [
                'has_warranty' => true,
                'is_active' => true,
                'days_remaining' => $daysRemaining,
                'label' => "Garansi Aktif ({$daysRemaining} hari tersisa)",
                'badge_class' => 'stamp-done',
            ];
        }

        return [
            'has_warranty' => true,
            'is_active' => false,
            'days_remaining' => 0,
            'label' => 'Masa Garansi Habis',
            'badge_class' => 'stamp-cancelled',
        ];
    }
}
