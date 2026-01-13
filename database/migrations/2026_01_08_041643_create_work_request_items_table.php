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
        Schema::create('work_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_request_id')->constrained('work_requests')->onDelete('cascade');
            $table->string('part_name');
            $table->double('qty');
            $table->string('unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_request_items');
    }
};
