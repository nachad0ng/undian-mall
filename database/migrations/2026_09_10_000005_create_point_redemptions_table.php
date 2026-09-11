<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 3e. Tabel penukaran_poin: satu baris = satu struk ditukar untuk satu hadiah
        Schema::create('point_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('raffle_period_id')->constrained('raffle_periods')->restrictOnDelete();
            $table->foreignId('prize_id')->constrained('prizes')->restrictOnDelete();
            $table->foreignId('purchase_id')->constrained('purchases')->restrictOnDelete()
                ->comment('Satu struk = satu baris di sini. Struk tidak bisa dipakai lagi setelah ini.');
            $table->foreignId('cs_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Petugas CS yang memproses penukaran');
            $table->dateTime('redeemed_at');
            $table->unsignedBigInteger('nominal_struk')->comment('Total belanja di struk yang ditukar');
            $table->unsignedBigInteger('total_poin_didapat')->comment('Poin yang didapat (nominal // nominal_per_poin + bonus)');
            $table->string('status', 20)->default('success')
                ->comment('success / failed / rejected');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['purchase_id'], 'point_redemptions_purchase_unique')
                ->comment('Satu struk hanya boleh dipakai sekali');
            $table->index(['customer_id', 'raffle_period_id']);
            $table->index(['customer_id', 'prize_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_redemptions');
    }
};
