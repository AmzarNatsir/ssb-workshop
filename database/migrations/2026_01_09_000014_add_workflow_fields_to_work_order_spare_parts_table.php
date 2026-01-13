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
        Schema::table('work_order_spare_parts', function (Blueprint $table) {
            // Validation (Step 2)
            $table->unsignedBigInteger('validated_by')->nullable()->after('status');
            $table->timestamp('validated_at')->nullable()->after('validated_by');

            // Return Workflow (Step 5)
            $table->decimal('qty_returned', 10, 2)->default(0)->after('qty_issued');
            $table->unsignedBigInteger('returned_by')->nullable()->after('qty_returned'); // Mechanic
            $table->timestamp('returned_at')->nullable()->after('returned_by');
            
            // Return Approval
            $table->string('return_status')->default('NONE')->after('status'); // NONE, PENDING, VALIDATED, APPROVED
            $table->unsignedBigInteger('return_validated_by')->nullable()->after('return_status');
            $table->timestamp('return_validated_at')->nullable()->after('return_validated_by');
            $table->unsignedBigInteger('return_approved_by')->nullable()->after('return_validated_by');
            $table->timestamp('return_approved_at')->nullable()->after('return_approved_by');

            $table->foreign('validated_by')->references('id')->on('users');
            $table->foreign('returned_by')->references('id')->on('users');
            $table->foreign('return_validated_by')->references('id')->on('users');
            $table->foreign('return_approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_spare_parts', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropForeign(['returned_by']);
            $table->dropForeign(['return_validated_by']);
            $table->dropForeign(['return_approved_by']);
            
            $table->dropColumn([
                'validated_by', 'validated_at',
                'qty_returned', 'returned_by', 'returned_at',
                'return_status', 'return_validated_by', 'return_validated_at',
                'return_approved_by', 'return_approved_at'
            ]);
        });
    }
};
