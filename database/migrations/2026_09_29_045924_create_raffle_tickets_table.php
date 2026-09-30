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
        Schema::create('raffle_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_period_id')->constrained('raffle_periods')->restrictOnDelete();
            $table->foreignId('prize_id')->constrained('prizes')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('point_redemption_id')->constrained('point_redemptions')->cascadeOnDelete();
            $table->foreignId('purchase_id')->constrained('purchases')->restrictOnDelete();
            $table->unsignedBigInteger('sequence_number')->comment('Nomor urut per hadiah, mulai dari 1');
            $table->string('ticket_number', 20)->comment('Nomor undian zero-pad sesuai ticket_digits, misal 010');
            $table->timestamps();

            $table->unique(['prize_id', 'sequence_number']);
            $table->unique(['prize_id', 'ticket_number']);
            $table->index(['prize_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raffle_tickets');
    }
};
