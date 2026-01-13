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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->enum('status', ['DRAFT', 'OPEN', 'IN_PROGRESS', 'READY', 'COMPLETED', 'CLOSED', 'CANCELLED'])->default('DRAFT')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('OPEN')->change();
        });
    }
};
