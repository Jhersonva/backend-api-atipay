<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'active', 'finished', 'rejected'])->default('pending');
            $table->text('admin_message')->nullable();
            $table->decimal('daily_earning', 10, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
