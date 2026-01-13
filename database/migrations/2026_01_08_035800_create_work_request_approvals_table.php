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
        Schema::create('work_request_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_request_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Who actually approved
            $table->unsignedBigInteger('role_id'); // Target role
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('comment')->nullable();
            $table->integer('step_order');
            $table->timestamps();

            $table->foreign('work_request_id')->references('id')->on('work_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_request_approvals');
    }
};
