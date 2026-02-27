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
        Schema::create('unit_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('request_no')->unique();
            $table->integer('project_id')->nullable();
            $table->string('status')->default('DRAFT'); // DRAFT, SUBMITTED, GA_VALIDATED, APPROVED, REJECTED, RFU, FINALIZED
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('ga_validated_by')->nullable();
            $table->unsignedBigInteger('om_approved_by')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('total_units')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('ga_validated_by')->references('id')->on('users');
            $table->foreign('om_approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_requests');
    }
};
