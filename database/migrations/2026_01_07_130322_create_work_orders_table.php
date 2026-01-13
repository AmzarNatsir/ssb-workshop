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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique(); // For external/URL reference
            $table->string('work_order_no')->unique(); // Format: WO/YYYYMM/SEQUENCE
            $table->unsignedBigInteger('service_plan_id')->nullable()->index();
            $table->unsignedBigInteger('equipment_id')->index();
            $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH'])->default('MEDIUM');
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('OPEN');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->index();
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('service_plan_id')->references('id')->on('service_plans')->onDelete('set null');
            $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
