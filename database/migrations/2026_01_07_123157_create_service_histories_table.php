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
        Schema::create('service_histories', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('service_plan_id')->constrained('service_plans')->onDelete('cascade');
            $table->decimal('hm_at_service', 10, 2)->comment('Actual HM when serviced');
            $table->date('service_date');
            $table->enum('service_type', ['General Service', 'Service Engine Oil']);
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            // Index for performance
            $table->index('service_plan_id');
            $table->index('service_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_histories');
    }
};
