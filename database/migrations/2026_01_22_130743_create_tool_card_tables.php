<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20)->unique();
            $table->string('name', 100);
            $table->string('position', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('tool_cards', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 36)->unique();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('access_level', ['1', '2', '3'])->comment('1: Basic, 2: Standard, 3: Full');
            $table->json('tool_categories')->nullable();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED_WSP', 'APPROVED_FINAL', 'REJECTED'])->default('DRAFT');
            $table->integer('current_approval_level')->default(0);
            $table->string('barcode_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('tool_card_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_card_id')->constrained('tool_cards')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users');
            $table->integer('level')->comment('1: WSP, 2: KA Plan');
            $table->enum('status', ['APPROVED', 'REJECTED', 'REVISION']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Seed dummy data for employees
        DB::table('employees')->insert([
            ['nik' => '198501012023031001', 'name' => 'Andi Wijaya', 'position' => 'Mechanic', 'department' => 'Workshop', 'created_at' => now(), 'updated_at' => now()],
            ['nik' => '199203152024042002', 'name' => 'Siti Nurhayati', 'position' => 'Admin', 'department' => 'Workshop', 'created_at' => now(), 'updated_at' => now()],
            ['nik' => '198911202021051003', 'name' => 'Budi Santoso', 'position' => 'Mechanic Lead', 'department' => 'Workshop', 'created_at' => now(), 'updated_at' => now()],
            ['nik' => '199708092025012004', 'name' => 'Dewi Lestari', 'position' => 'Helper', 'department' => 'Workshop', 'created_at' => now(), 'updated_at' => now()],
            ['nik' => '199502272022061005', 'name' => 'Rizky Maulana', 'position' => 'Welder', 'department' => 'Workshop', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_card_approvals');
        Schema::dropIfExists('tool_cards');
        Schema::dropIfExists('employees');
    }
};
