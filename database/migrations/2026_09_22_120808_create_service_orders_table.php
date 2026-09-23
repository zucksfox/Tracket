<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('service_code', 30)->unique()->index();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_name', 150);
            $table->string('device_serial', 100)->nullable();
            $table->text('issue_description');
            $table->string('accessories_included', 255)->nullable();
            $table->enum('status', ['pending', 'diagnosing', 'in_progress', 'ready', 'completed', 'cancelled'])->default('pending')->index();
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->integer('warranty_days')->default(0);
            $table->date('warranty_expires_at')->nullable();
            $table->text('technician_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
