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
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_period_id')->constrained('raffle_periods')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('sequence')->default(1)->comment('Urutan undian / ranking hadiah, misal 1 untuk Hadiah Utama');
            $table->string('status', 20)->default('active')->comment('active, inactive');
            $table->timestamps();

            $table->index(['raffle_period_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};
