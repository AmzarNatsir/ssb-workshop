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
        Schema::create('inspection_result_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('result_id');
            $table->unsignedBigInteger('item_id');
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 10, 2)->nullable();
            $table->string('value_option', 50)->nullable();
            $table->string('image_path')->nullable();
            $table->text('notes')->nullable();
            $table->json('triggered_action')->nullable();
            $table->timestamps();

            $table->foreign('result_id')->references('id')->on('inspection_results')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('inspection_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_result_items');
    }
};
