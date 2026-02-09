<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value');
            $table->string('description', 500)->nullable();
            $table->timestamps();
            
            $table->index('setting_key');
        });
        
        // Seed default settings
        DB::table('system_settings')->insert([
            [
                'setting_key' => 'loan_duration_hours',
                'setting_value' => '24',
                'description' => 'Default loan duration in hours',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'reminder_before_hours',
                'setting_value' => '2',
                'description' => 'Hours before due date to send first reminder',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'overdue_check_interval_minutes',
                'setting_value' => '30',
                'description' => 'Interval to check for overdue loans in minutes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
