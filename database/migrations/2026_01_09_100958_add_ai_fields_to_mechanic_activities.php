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
        Schema::table('mechanic_activities', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable()->after('description');
            $table->dateTime('end_time')->nullable()->after('start_time');
            $table->decimal('working_duration', 8, 2)->nullable()->after('end_time');
            $table->text('activity_summary')->nullable()->after('status');
            $table->string('validation_status')->default('PENDING')->after('activity_summary'); // VALID, INVALID, PENDING
            $table->text('ai_notes')->nullable()->after('validation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mechanic_activities', function (Blueprint $table) {
            $table->dropColumn([
                'start_time',
                'end_time',
                'working_duration',
                'activity_summary',
                'validation_status',
                'ai_notes'
            ]);
        });
    }
};
