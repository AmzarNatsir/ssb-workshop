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
        Schema::table('tools', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable()->after('tool_type_id');
            $table->integer('min_quantity')->default(0)->after('quantity');
            
            // Assuming common_status table exists and has an id column
             $table->foreign('status_id')->references('id')->on('common_status')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
             $table->dropForeign(['status_id']);
            $table->dropColumn(['status_id', 'min_quantity']);
        });
    }
};
