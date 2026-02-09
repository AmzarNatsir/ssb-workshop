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
        Schema::create('inspection_section_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('result_id');
            $table->unsignedBigInteger('section_id');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->foreign('result_id')->references('id')->on('inspection_results')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('inspection_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_section_results');
    }
};
