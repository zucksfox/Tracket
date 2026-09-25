<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Catatan audit perubahan data.
 *
 * Satu tabel ini melayani semua model yang memakai trait LogsActivity.
 * Hubungan ke model sumber memakai relasi polymorphic (morphTo): kolom
 * subject_type menyimpan nama kelas model (Customer, Sparepart,
 * ServiceOrder), kolom subject_id menyimpan kunci barisnya. Dengan cara ini
 * penambahan model baru tidak memerlukan tabel log baru maupun kolom
 * tambahan — cukup pakai trait-nya.
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'action',
        'causer_id',
        'properties',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * Model yang dicatat perubahannya.
     *
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Pengguna yang melakukan perubahan; null bila dijalankan seeder/konsol.
     *
     * @return BelongsTo<User, $this>
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
