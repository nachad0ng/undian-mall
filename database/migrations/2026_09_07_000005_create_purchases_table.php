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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_period_id')->constrained('raffle_periods')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('entered_by')->constrained('users')->restrictOnDelete();
            $table->string('receipt_number', 100);
            $table->dateTime('purchased_at')->comment('Tanggal & jam transaksi sesuai struk');
            $table->unsignedBigInteger('amount')->comment('Nominal belanja dalam Rupiah');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('payment_type_id')->nullable()
                ->comment('Metode pembayaran yang digunakan dalam transaksi ini');
            $table->string('exchange_status', 20)->default('belum')
                ->comment('belum / sudah — apakah struk ini sudah ditukar poinnya');
            $table->softDeletes();
            $table->timestamps();

            // Mencegah satu struk dari tenant yang sama digunakan berulang kali pada periode yang sama
            $table->unique(['raffle_period_id', 'tenant_id', 'receipt_number'], 'purchases_period_tenant_receipt_unique');
            $table->index(['customer_id', 'raffle_period_id']);
            $table->index(['tenant_id', 'purchased_at']);
            $table->index(['exchange_status', 'payment_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
