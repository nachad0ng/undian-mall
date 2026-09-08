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
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drawing_id')->constrained('drawings')->cascadeOnDelete();
            $table->foreignId('raffle_period_id')->constrained('raffle_periods')->restrictOnDelete();
            $table->foreignId('prize_id')->constrained('prizes')->restrictOnDelete();
            $table->foreignId('coupon_id')->unique()->constrained('coupons')->restrictOnDelete()->comment('1 kupon hanya dapat memenangkan 1 hadiah');
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->dateTime('won_at');
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['raffle_period_id', 'is_published']);
            $table->index(['customer_id', 'raffle_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
