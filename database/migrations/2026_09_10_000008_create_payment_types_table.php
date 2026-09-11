<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 3i. Tabel tipe_pembayaran: master metode pembayaran
        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed data awal (opsional, bisa dipisah ke seeder)
        // Kita tinggal biarkan kosong dan diisi via seeder/admin nanti
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_types');
    }
};
