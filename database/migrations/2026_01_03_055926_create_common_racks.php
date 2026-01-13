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
        Schema::create('common_racks', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('rack_code');
            $table->string('name');
            $table->string('location');
            $table->string('responsible_person')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('common_racks');
    }
};
