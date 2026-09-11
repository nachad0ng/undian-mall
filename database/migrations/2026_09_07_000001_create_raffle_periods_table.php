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
        Schema::create('raffle_periods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->dateTime('exchange_start_at')->nullable()
                ->comment('Rentang boleh tukar struk ke CS (default sama dengan end_at)');
            $table->dateTime('exchange_end_at')->nullable()
                ->comment('Batas waktu penukaran struk (default sama dengan end_at)');
            $table->string('status', 20)->default('active')
                ->comment('draft, active, inactive, closed');
            $table->string('drawing_status', 20)->default('pending')
                ->comment('pending, in_progress, completed');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'start_at', 'end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raffle_periods');
    }
};
