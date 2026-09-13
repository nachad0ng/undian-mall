<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_redemptions', function (Blueprint $table) {
            $table->unsignedBigInteger('nominal_per_poin_snapshot')->nullable()
                ->after('nominal_struk');
            $table->unsignedBigInteger('poin_dari_nominal')->nullable()
                ->after('nominal_per_poin_snapshot');
            $table->unsignedBigInteger('poin_bonus_pembayaran')->nullable()
                ->after('poin_dari_nominal');
            $table->unsignedBigInteger('bonus_rule_id_snapshot')->nullable()
                ->after('poin_bonus_pembayaran');
            $table->string('payment_type_code_snapshot')->nullable()
                ->after('bonus_rule_id_snapshot');
            $table->string('payment_type_name_snapshot')->nullable()
                ->after('payment_type_code_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('point_redemptions', function (Blueprint $table) {
            $table->dropColumn([
                'nominal_per_poin_snapshot',
                'poin_dari_nominal',
                'poin_bonus_pembayaran',
                'bonus_rule_id_snapshot',
                'payment_type_code_snapshot',
                'payment_type_name_snapshot',
            ]);
        });
    }
};
