<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('wo_type')->nullable()->after('work_order_no');
            $table->string('service_category')->nullable()->after('wo_type');
            $table->string('maintenance_type')->nullable()->after('service_category');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('status')->index();
            
            // Re-defining status with new values
            $table->enum('status', ['DRAFT', 'OPEN', 'IN_PROGRESS', 'READY', 'COMPLETED', 'CLOSED', 'CANCELLED'])->default('DRAFT')->change();

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['wo_type', 'service_category', 'maintenance_type', 'assigned_to']);
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('OPEN')->change();
        });
    }
};
