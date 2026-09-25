<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasPaymentFields(): bool
    {
        // Older MariaDB lacks generation_expression used by Laravel's MySQL introspection.
        if (DB::connection()->getDriverName() === 'mysql') {
            return count(DB::select("SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'service_orders' AND column_name = 'payment_status'")) > 0;
        }

        return Schema::hasColumn('service_orders', 'payment_status');
    }

    public function up(): void
    {
        if (! $this->hasPaymentFields()) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->string('payment_status', 20)->default('unpaid')->index();
                $table->string('payment_method', 20)->nullable();
                $table->timestamp('paid_at')->nullable();
            });
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(255) NOT NULL DEFAULT 'technician'");
        } else {
            // Laravel rebuilds the legacy SQLite enum CHECK without losing rows.
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('technician')->change();
            });
        }
    }

    public function down(): void
    {
        if ($this->hasPaymentFields()) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->dropColumn(['payment_status', 'payment_method', 'paid_at']);
            });
        }
        // Keep expanded roles: narrowing would destroy cashier accounts.
    }
};
