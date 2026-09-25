<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Jejak audit ringan lewat event Eloquent.
 *
 * Pasang trait ini pada model mana pun yang perlu dicatat perubahannya.
 * Pencatatan memakai relasi polymorphic: ActivityLog menunjuk balik ke model
 * sumber lewat pasangan kolom subject_type + subject_id, sehingga satu tabel
 * cukup untuk semua model.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            $model->writeLog('created', $model->toArray());
        });

        static::updated(function (Model $model) {
            $changed = $model->getChanges();
            // Abaikan pembaruan yang hanya menyentuh kolom waktu.
            unset($changed['updated_at']);
            if (empty($changed)) {
                return;
            }
            $model->writeLog('updated', $changed, $model->getOriginal());
        });

        static::deleted(function (Model $model) {
            $model->writeLog('deleted', ['id' => $model->getKey()]);
        });
    }

    /**
     * Seluruh catatan audit milik model ini, terbaru lebih dahulu.
     *
     * @return MorphMany<ActivityLog, $this>
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest('id');
    }

    protected function writeLog(string $action, array $newData, ?array $oldData = null): void
    {
        try {
            ActivityLog::create([
                // getMorphClass() mengembalikan alias pendek dari morph map
                // (misalnya "customer"), bukan nama kelas lengkap, sehingga
                // nama kelas boleh diubah tanpa merusak catatan lama.
                'subject_type' => $this->getMorphClass(),
                'subject_id' => $this->getKey(),
                'action' => $action,
                'causer_id' => Auth::id(),
                'properties' => [
                    'new' => $newData,
                    'old' => $oldData,
                ],
            ]);
        } catch (Throwable) {
            // Pencatatan audit tidak boleh menggagalkan transaksi utama.
        }
    }
}
