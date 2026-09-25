<?php

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\Sparepart;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyeragamkan isi kolom activity_logs.subject_type menjadi alias pendek.
 *
 * Sebelumnya trait LogsActivity menyimpan nama kelas lengkap
 * ("App\Models\Customer"). Setelah morph map diterapkan di AppServiceProvider,
 * catatan baru memakai alias pendek ("customer"), sehingga baris lama perlu
 * disamakan agar pencarian jejak audit tidak terbelah dua format.
 */
return new class extends Migration
{
    /**
     * @var array<class-string, string>
     */
    private array $map = [
        Customer::class => 'customer',
        Sparepart::class => 'sparepart',
        ServiceOrder::class => 'service_order',
    ];

    public function up(): void
    {
        foreach ($this->map as $class => $alias) {
            DB::table('activity_logs')
                ->where('subject_type', $class)
                ->update(['subject_type' => $alias]);
        }
    }

    public function down(): void
    {
        foreach ($this->map as $class => $alias) {
            DB::table('activity_logs')
                ->where('subject_type', $alias)
                ->update(['subject_type' => $class]);
        }
    }
};
