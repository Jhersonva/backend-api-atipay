<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'partner'])->default('partner');
            $table->string('password');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('accumulated_points')->default(0);
            $table->string('reference_code')->unique();
            $table->foreignId('referred_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

