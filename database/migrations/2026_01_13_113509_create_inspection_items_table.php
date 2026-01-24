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
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->integer('item_order')->default(0);
            $table->string('item_code', 50);
            $table->string('item_name', 255);
            $table->text('item_description')->nullable();
            $table->enum('input_type', [
                'NUMBER',
                'TEXT',
                'GOOD_REPAIR_REPLACE_NA',
                'YES_NO_NA',
                'PASS_FAIL_NA',
                'OK_FAULTY_NA',
                'IMAGE',
                'DATE'
            ]);
            $table->boolean('is_required')->default(false);
            $table->decimal('threshold_warning', 10, 2)->nullable();
            $table->decimal('threshold_critical', 10, 2)->nullable();
            $table->json('conditional_logic')->nullable();
            $table->json('auto_action')->nullable();
            $table->text('instruction')->nullable();
            $table->string('reference_image')->nullable();
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('inspection_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_items');
    }
};
