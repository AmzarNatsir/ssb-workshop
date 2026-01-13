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
        Schema::create('service_plans', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('equipment_id')->constrained('equipments')->onDelete('restrict');
            $table->decimal('hm_ps_sebelumnya', 10, 2)->comment('HM Previous Service');
            $table->decimal('hm_actual', 10, 2)->comment('HM Actual');
            $table->decimal('wh_project', 10, 2)->comment('WH per Project (snapshot)');
            $table->decimal('overdue', 10, 2)->comment('Calculated Overdue');
            $table->decimal('ps_berikutnya', 10, 2)->comment('Next Service HM');
            $table->date('plan_date')->comment('Calculated Plan Date');
            $table->enum('service_type', ['General Service', 'Service Engine Oil']);
            $table->enum('status', ['PLANNED', 'OVERDUE', 'COMPLETED', 'CANCELLED'])->default('PLANNED');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('equipment_id');
            $table->index('status');
            $table->index('is_active');
            $table->index('plan_date');
            
            // Note: We'll enforce one active plan per equipment via application logic
            // MySQL doesn't support partial indexes like PostgreSQL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plans');
    }
};
