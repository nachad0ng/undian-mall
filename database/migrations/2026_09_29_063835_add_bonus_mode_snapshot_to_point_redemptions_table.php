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
        Schema::table('point_redemptions', function (Blueprint $table) {
            $table->string('bonus_mode_snapshot', 10)->nullable()->comment('Mode bonus saat redemption: add / multiply');
            $table->decimal('bonus_multiplier_snapshot', 8, 2)->nullable()->comment('Pengali saat redemption untuk mode multiply');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_redemptions', function (Blueprint $table) {
            $table->dropColumn(['bonus_mode_snapshot', 'bonus_multiplier_snapshot']);
        });
    }
};
