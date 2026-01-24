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
        Schema::create('inspection_results', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 50)->unique();
            $table->string('result_code', 50)->unique();
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('inspector_id');
            $table->dateTime('inspection_date');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->enum('overall_status', ['PASS', 'FAIL', 'PENDING'])->default('PENDING');
            $table->boolean('unit_ready_for_operation')->nullable();
            $table->unsignedBigInteger('supervisor_approval_id')->nullable();
            $table->timestamp('supervisor_approval_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('inspection_schedules')->onDelete('set null');
            $table->foreign('form_id')->references('id')->on('inspection_forms')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('equipments')->onDelete('cascade');
            $table->foreign('inspector_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supervisor_approval_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_results');
    }
};
