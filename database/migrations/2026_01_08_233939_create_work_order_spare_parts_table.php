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
        Schema::create('work_order_spare_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_order_id')->index();
            $table->string('part_name');
            $table->decimal('qty_requested', 10, 2);
            $table->decimal('qty_issued', 10, 2)->default(0);
            $table->enum('status', ['PENDING', 'APPROVED', 'ISSUED', 'RETURN_PENDING', 'RETURNED', 'CANCELLED'])->default('PENDING');
            $table->unsignedBigInteger('issued_by')->nullable()->index();
            $table->timestamp('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('cascade');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_spare_parts');
    }
};
