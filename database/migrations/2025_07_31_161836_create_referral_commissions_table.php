<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // quien gana la comisión
            $table->foreignId('referred_user_id')->constrained('users')->onDelete('cascade'); // quien realizó la compra o acción
            $table->unsignedTinyInteger('level'); // nivel (1 a 5)
            $table->decimal('commission_amount', 10, 2); // monto en soles
            $table->unsignedInteger('points_generated'); // puntos generados por el referido
            $table->enum('source_type', ['purchase', 'investment'])->default('purchase'); // tipo de acción que generó la comisión
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};
