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
        Schema::create('work_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('wr_no')->unique(); // Format: WR000001
            $table->enum('category', [
                'On-Project – Operation',
                'Non-Project – Operation',
                'Non-Project – Non-Operation',
                'Non-Asset',
                'Project',
                'Department'
            ]);
            $table->unsignedBigInteger('equipment_id')->nullable()->index();
            $table->string('operator_name')->nullable();
            $table->double('hm_km')->nullable();
            $table->text('asset_condition')->nullable();
            $table->text('trouble_description')->nullable();
            $table->string('location')->nullable();
            $table->enum('type', ['Goods Request', 'Repair Request']);
            $table->enum('status', ['DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'IN_WORK_ORDER', 'CLOSED'])->default('DRAFT');
            $table->unsignedBigInteger('created_by')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_requests');
    }
};
