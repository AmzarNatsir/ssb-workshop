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
        Schema::create('tool_stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_no', 50)->unique();
            $table->enum('period', ['MONTHLY', 'QUARTERLY', 'YEARLY']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['DRAFT', 'IN_PROGRESS', 'FINAL'])->default('DRAFT');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('tool_stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_id')->constrained('tool_stock_opnames')->onDelete('cascade');
            $table->foreignId('tool_id')->constrained('tools');
            $table->enum('physical_exist', ['EXISTS', 'NOT_EXISTS']);
            $table->enum('physical_condition', ['GOOD', 'DAMAGE', 'LOST']);
            $table->string('physical_location', 100)->nullable();
            $table->enum('system_status', ['AVAILABLE', 'BORROWED', 'DAMAGED', 'LOST']);
            $table->boolean('mismatch')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tool_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('tools');
            $table->foreignId('opname_id')->constrained('tool_stock_opnames');
            $table->enum('adjustment_type', ['STATUS', 'LOCATION', 'STOCK']);
            $table->string('old_value', 100)->nullable();
            $table->string('new_value', 100)->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('adjusted_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_adjustments');
        Schema::dropIfExists('tool_stock_opname_details');
        Schema::dropIfExists('tool_stock_opnames');
    }
};
