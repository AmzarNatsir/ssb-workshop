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
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string("code", 50)->unique();
            $table->string('name', 200);
            $table->string('description', 200)->nullable();
            $table->string('engine_no', 50)->nullable();
            $table->string('chassis_no', 50)->nullable();
            $table->string('plate_number', 50)->nullable();
            $table->string('capacity', 100)->nullable();
            $table->string('prodution_year', 4)->nullable();
            $table->date('warranty_date')->nullable();
            $table->date('purchase_date')->nullable();
            $table->double('purchase_price')->nullable();
            $table->double('internal_estimated_price')->nullable();
            $table->double('market_price')->nullable();
            $table->integer('equipment_status_id')->nullable();
            $table->string('status_information', 200)->nullable();
            $table->integer('project_id')->nullable();
            $table->string('project_status', 50)->nullable();
            $table->integer('meter_reading_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->integer('pic_unit')->nullable();
            $table->integer('ownership_mode_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('merk_id')->nullable();
            $table->unsignedBigInteger('unit_type_id')->nullable();
            $table->string('image', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
