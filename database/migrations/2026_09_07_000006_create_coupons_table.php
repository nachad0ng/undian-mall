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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_period_id')->constrained('raffle_periods')->restrictOnDelete();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('coupon_number', 100)->unique();
            $table->string('status', 20)->default('active')->comment('active, won, void');
            $table->timestamps();

            $table->index(['raffle_period_id', 'status'], 'coupons_period_status_index');
            $table->index(['customer_id', 'raffle_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
