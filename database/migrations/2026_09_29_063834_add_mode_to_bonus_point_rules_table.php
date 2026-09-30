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
        Schema::table('bonus_point_rules', function (Blueprint $table) {
            $table->string('mode', 10)->default('add')->comment('add = tambah poin, multiply = kali lipat poin nominal');
            $table->decimal('multiplier', 8, 2)->nullable()->comment('Pengali untuk mode multiply, misal 2.00 = 2x');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonus_point_rules', function (Blueprint $table) {
            $table->dropColumn(['mode', 'multiplier']);
        });
    }
};
