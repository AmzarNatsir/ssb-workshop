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
        Schema::table('work_order_spare_parts', function (Blueprint $table) {
            $table->enum('status', [
                'PENDING', 
                'VALIDATED', 
                'APPROVED', 
                'ISSUED', 
                'REJECTED', 
                'RETURN_PENDING', 
                'RETURNED', 
                'CANCELLED'
            ])->default('PENDING')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_spare_parts', function (Blueprint $table) {
            $table->enum('status', [
                'PENDING', 
                'APPROVED', 
                'ISSUED', 
                'RETURN_PENDING', 
                'RETURNED', 
                'CANCELLED'
            ])->default('PENDING')->change();
        });
    }
};
