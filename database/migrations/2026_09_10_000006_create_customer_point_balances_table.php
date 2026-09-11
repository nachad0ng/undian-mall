<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 3g. Tabel saldo_poin_customer: rekap per customer per periode per hadiah
        Schema::create('customer_point_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('raffle_period_id')->constrained('raffle_periods')->restrictOnDelete();
            $table->foreignId('prize_id')->constrained('prizes')->restrictOnDelete();
            $table->unsignedBigInteger('total_poin')->default(0);
            $table->timestamps();

            $table->unique(['customer_id', 'raffle_period_id', 'prize_id'],
                'customer_point_balances_customer_period_prize_unique');
            $table->index(['customer_id', 'raffle_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_point_balances');
    }
};
