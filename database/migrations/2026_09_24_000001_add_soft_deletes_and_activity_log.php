<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Activity log for audit trail (BNSP compliance)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('action', 50); // created, updated, deleted
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->text('properties')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'action']);
            $table->index('causer_id');
        });

        // Soft deletes for key tables
        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('spareparts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('spareparts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
