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
        Schema::table('unit_requests', function (Blueprint $table) {
            $table->string('project_name')->nullable()->after('project_id');
            $table->unsignedBigInteger('requested_by')->nullable()->change();
        });

        Schema::table('unit_request_items', function (Blueprint $table) {
            $table->unsignedBigInteger('equipment_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_requests', function (Blueprint $table) {
            $table->dropColumn('project_name');
            $table->unsignedBigInteger('requested_by')->nullable(false)->change();
        });

        Schema::table('unit_request_items', function (Blueprint $table) {
            $table->unsignedBigInteger('equipment_id')->nullable(false)->change();
        });
    }
};
