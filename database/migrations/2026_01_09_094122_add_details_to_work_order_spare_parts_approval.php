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
            $table->string('picking_mechanic')->nullable()->after('qty_issued');
            $table->text('return_reason')->nullable()->after('return_status');
            $table->unsignedBigInteger('return_rejected_by')->nullable()->after('return_approved_at');
            $table->foreign('return_rejected_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_spare_parts', function (Blueprint $table) {
            $table->dropForeign(['return_rejected_by']);
            $table->dropColumn(['picking_mechanic', 'return_reason', 'return_rejected_by']);
        });
    }
};
