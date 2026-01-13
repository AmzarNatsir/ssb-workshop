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
        Schema::create('work_request_approval_rules', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('wr_type');
            $table->unsignedBigInteger('role_id');
            $table->integer('step_order');
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_request_approval_rules');
    }
};
