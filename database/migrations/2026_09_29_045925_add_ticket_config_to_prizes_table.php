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
        Schema::table('prizes', function (Blueprint $table) {
            $table->unsignedTinyInteger('ticket_digits')->default(3)->comment('Jumlah digit nomor undian bola, misal 3 -> 000-999');
            $table->unsignedBigInteger('ticket_counter')->default(0)->comment('Sequence terakhir nomor undian yang diterbitkan per hadiah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prizes', function (Blueprint $table) {
            $table->dropColumn(['ticket_digits', 'ticket_counter']);
        });
    }
};
