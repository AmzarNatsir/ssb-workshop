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
        Schema::table('tool_cards', function (Blueprint $table) {
            $table->enum('code_type', ['QR', 'BARCODE'])->default('QR')->after('access_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tool_cards', function (Blueprint $table) {
            $table->dropColumn('code_type');
        });
    }
};
