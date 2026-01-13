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
        Schema::table('part_requirements', function (Blueprint $table) {
            $table->dropForeign(['periodic_service_type_id']);
            $table->dropColumn('periodic_service_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('part_requirements', function (Blueprint $table) {
            $table->unsignedBigInteger('periodic_service_type_id')->nullable();
            $table->foreign('periodic_service_type_id')->references('id')->on('periodic_service_type')->onDelete('cascade');
        });
    }
};
