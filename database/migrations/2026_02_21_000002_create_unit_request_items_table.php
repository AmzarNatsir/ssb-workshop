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
        Schema::create('unit_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_request_id');
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('mechanic_id')->nullable();
            $table->unsignedBigInteger('work_request_id')->nullable(); // Link to commissioning WR
            $table->string('status')->default('PENDING'); // PENDING, ASSIGNED, RFU, INSPECTED, FINALIZED
            $table->unsignedBigInteger('p2h_result_id')->nullable(); // Link to InspectionResult
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->double('hm_start')->nullable();
            $table->double('km_start')->nullable();
            $table->string('fuel_level')->nullable();
            $table->string('refuel_status')->nullable();
            $table->text('attachment_paths')->nullable(); // JSON or serialized paths
            $table->timestamps();

            $table->foreign('unit_request_id')->references('id')->on('unit_requests')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('equipments');
            $table->foreign('mechanic_id')->references('id')->on('users');
            $table->foreign('operator_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_request_items');
    }
};
