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
        Schema::create('loan_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 50)->unique()->comment('Format: LN-YYYYMMDD-NNNN');
            $table->foreignId('tool_card_id')->constrained('tool_cards')->onDelete('restrict');
            $table->foreignId('tool_id')->constrained('tools')->onDelete('restrict');
            $table->foreignId('admin_id')->constrained('users')->onDelete('restrict')
                ->comment('User who processed the loan');
            $table->enum('requirement_type', ['Work Order', 'Other'])->default('Other');
            $table->string('requirement_reference', 255)->nullable()
                ->comment('WO number or other reference');
            $table->text('requirement_notes')->nullable();
            $table->timestamp('loan_date');
            $table->timestamp('expected_return_date');
            $table->timestamp('actual_return_date')->nullable();
            $table->enum('status', ['Active', 'Returned', 'Overdue', 'Lost'])->default('Active');
            $table->enum('return_condition', ['Good', 'Damaged', 'Lost'])->nullable();
            $table->text('return_notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('loan_number');
            $table->index('tool_card_id');
            $table->index('tool_id');
            $table->index('status');
            $table->index('loan_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_transactions');
    }
};
