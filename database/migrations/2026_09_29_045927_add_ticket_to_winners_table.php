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
        Schema::table('winners', function (Blueprint $table) {
            $table->foreignId('raffle_ticket_id')->nullable()->after('customer_id')->constrained('raffle_tickets')->nullOnDelete();
            $table->string('winning_number', 20)->nullable()->after('raffle_ticket_id')->comment('Snapshot nomor bola pemenang, misal 010');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('raffle_ticket_id');
            $table->dropColumn('winning_number');
        });
    }
};
