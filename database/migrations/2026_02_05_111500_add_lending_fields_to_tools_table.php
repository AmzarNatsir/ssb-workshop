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
            $table->enum('required_access_level', ['1', '2', '3'])->default('1')->after('tool_type_id')
                ->comment('1: Basic, 2: Standard, 3: Full');
            $table->string('barcode', 100)->unique()->nullable()->after('uid');
            $table->string('serial_number', 100)->nullable()->after('code');
            $table->string('shelf_location', 100)->nullable()->after('racks_id')
                ->comment('Alternative to racks for specific shelf location');
            
            $table->index('barcode');
            $table->index('required_access_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropIndex(['barcode']);
            $table->dropIndex(['required_access_level']);
            $table->dropColumn(['required_access_level', 'barcode', 'serial_number', 'shelf_location']);
        });
    }
};
