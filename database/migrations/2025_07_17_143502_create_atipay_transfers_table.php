<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atipay_transfers', function (Blueprint $table) {
            $table->id();

            // Relación con usuarios
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

            // Monto de la transferencia
            $table->integer('amount');

            // Tipo de atipay usado (tienda o inversión)
            $table->enum('type', ['investment', 'store']);

            // Confirmación del receptor
            $table->boolean('confirmed')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atipay_transfers');
    }
};
