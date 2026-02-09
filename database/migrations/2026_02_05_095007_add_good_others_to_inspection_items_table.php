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
        DB::statement("ALTER TABLE inspection_items CHANGE COLUMN input_type input_type ENUM('NUMBER', 'TEXT', 'GOOD_REPAIR_REPLACE_NA', 'YES_NO_NA', 'PASS_FAIL_NA', 'OK_FAULTY_NA', 'IMAGE', 'DATE', 'GOOD_OTHERS') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ensuring no data loss if possible, but standard revert removes the new option
        DB::statement("ALTER TABLE inspection_items CHANGE COLUMN input_type input_type ENUM('NUMBER', 'TEXT', 'GOOD_REPAIR_REPLACE_NA', 'YES_NO_NA', 'PASS_FAIL_NA', 'OK_FAULTY_NA', 'IMAGE', 'DATE') NOT NULL");
    }
};
