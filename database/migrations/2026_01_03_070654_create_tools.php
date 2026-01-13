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
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('description');
            $table->date('acquisition_date')->nullable();
            $table->double('acquisition_cost')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('image');
            $table->integer('racks_id');
            $table->integer('tool_type_id');
            $table->date('print_date')->nullable();
            $table->boolean('print_barcode')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
