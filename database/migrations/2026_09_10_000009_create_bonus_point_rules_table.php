<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 3j. Tabel rule_bonus_poin: bonus poin per tipe pembayaran per periode
        Schema::create('bonus_point_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_period_id')->constrained('raffle_periods')->restrictOnDelete();
            $table->foreignId('payment_type_id')->constrained('payment_types')->restrictOnDelete();
            $table->unsignedBigInteger('bonus_poin');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['raffle_period_id', 'payment_type_id'],
                'bonus_point_rules_period_payment_unique');
            $table->index(['raffle_period_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_point_rules');
    }
};
