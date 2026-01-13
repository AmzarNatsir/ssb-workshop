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
        Schema::create('part_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('periodic_service_type_id');
            $table->text('description')->nullable();
            $table->string('status')->default('Active'); // Active, Inactive
            $table->timestamps();

            // Foreign keys
            // Assuming equipments table exists and uses id
            // $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');
            // Assuming periodic_service_type table exists
             $table->foreign('periodic_service_type_id')->references('id')->on('periodic_service_type')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_requirements');
    }
};
