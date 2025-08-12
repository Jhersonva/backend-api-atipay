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
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('password');
            $table->enum('status', allowed: ['active', 'inactive'])->default('active');
            $table->decimal('atipay_money', 10, 2)->default(0); 
            $table->integer('accumulated_points')->default(0);
            $table->decimal('withdrawable_balance', 10, 2)->default(0);
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

