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
        Schema::create('drawings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_period_id')->constrained('raffle_periods')->restrictOnDelete();
            $table->foreignId('prize_id')->nullable()->constrained('prizes')->nullOnDelete();
            $table->foreignId('executed_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('executed_at');
            $table->string('status', 20)->default('completed')->comment('completed, cancelled');
            $table->json('metadata')->nullable()->comment('Audit information: total pool, seed, filter parameters');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['raffle_period_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drawings');
    }
};
