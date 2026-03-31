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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            $table->enum('status', ['pendente', 'pago', 'expirado', 'falhado'])->default('pendente');
            $table->enum('method', ['mbway', 'multibanco']);
            $table->string('mbway_phone')->nullable();
            $table->string('mb_entity')->nullable();
            $table->string('mb_reference')->nullable();
            $table->string('easypay_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
