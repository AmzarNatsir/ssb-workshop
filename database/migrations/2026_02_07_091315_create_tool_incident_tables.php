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
        // Tool Incident Reports
        Schema::create('tool_incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('incident_number')->unique();
            $table->enum('incident_type', ['MISSING', 'DAMAGED']);
            $table->foreignId('tool_id')->constrained('tools');
            $table->foreignId('loan_transaction_id')->constrained('loan_transactions');
            $table->foreignId('employee_id')->constrained('employees');
            
            // Incident Details
            $table->date('incident_date')->nullable();
            $table->text('description')->nullable();
            $table->text('chronology')->nullable();
            $table->string('last_location')->nullable();
            $table->string('last_condition')->nullable();
            
            // Financial & Installments
            $table->decimal('replacement_cost', 15, 2)->nullable();
            $table->integer('installment_months')->nullable(); // Duration
            $table->decimal('monthly_installment_amount', 15, 2)->nullable();
            $table->date('installment_start_month')->nullable();
            $table->date('installment_end_month')->nullable();
            
            // Status & Approvals
            $table->enum('status', [
                'DRAFT', 
                'SUBMITTED', 
                'APPROVED_MTN', 
                'APPROVED_HR', 
                'REJECTED'
            ])->default('DRAFT');
            
            $table->string('document_path')->nullable(); // Signed statement
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('approved_mtn_by')->nullable()->constrained('users');
            $table->timestamp('approved_mtn_at')->nullable();
            $table->foreignId('approved_hr_by')->nullable()->constrained('users');
            $table->timestamp('approved_hr_at')->nullable();
            
            $table->timestamps();
        });

        // Tool Incident Installments
        Schema::create('tool_incident_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_incident_report_id')->constrained('tool_incident_reports')->onDelete('cascade');
            $table->integer('period_month');
            $table->integer('period_year');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['PENDING', 'PAID'])->default('PENDING');
            $table->string('payroll_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_incident_installments');
        Schema::dropIfExists('tool_incident_reports');
    }
};
