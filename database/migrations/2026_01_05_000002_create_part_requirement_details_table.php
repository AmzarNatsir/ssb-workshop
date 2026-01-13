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
        Schema::create('part_requirement_details', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique(); // Although strictly not needed for details if using ID, standardizing.
            $table->unsignedBigInteger('part_requirement_id');
            $table->unsignedBigInteger('part_id'); // References parts_temp
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->foreign('part_requirement_id')->references('id')->on('part_requirements')->onDelete('cascade');
            // Assuming parts_temp table
             $table->foreign('part_id')->references('id')->on('parts_temp')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_requirement_details');
    }
};
