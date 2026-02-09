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
        Schema::create('loan_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_transaction_id')->constrained('loan_transactions')->onDelete('cascade');
            $table->enum('reminder_type', ['First Reminder', 'Second Reminder', 'Overdue Notice']);
            $table->timestamp('sent_at');
            $table->string('sent_to', 255)->nullable()->comment('Email or contact info');
            $table->enum('status', ['Sent', 'Failed', 'Pending'])->default('Pending');
            $table->timestamps();
            
            $table->index('loan_transaction_id');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_reminders');
    }
};
