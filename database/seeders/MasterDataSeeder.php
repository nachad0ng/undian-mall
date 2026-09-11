<?php

namespace Database\Seeders;

use App\Models\BonusPointRule;
use App\Models\PaymentType;
use App\Models\Prize;
use App\Models\RafflePeriod;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $period = RafflePeriod::updateOrCreate(
            ['code' => 'MALL-2026'],
            [
                'name' => 'Mall Lucky Draw 2026',
                'description' => 'Periode undian dan penukaran poin tahun 2026.',
                'start_at' => '2026-09-01 00:00:00',
                'end_at' => '2026-12-31 23:59:59',
                'exchange_start_at' => '2026-09-01 00:00:00',
                'exchange_end_at' => '2026-12-31 23:59:59',
                'status' => 'active',
                'drawing_status' => 'pending',
            ]
        );

        $prizes = [
            ['name' => 'Mobil', 'description' => 'Hadiah utama berupa mobil.', 'quantity' => 1, 'sequence' => 1, 'nominal_per_poin' => 1_000_000],
            ['name' => 'Motor', 'description' => 'Hadiah motor untuk pemenang.', 'quantity' => 2, 'sequence' => 2, 'nominal_per_poin' => 250_000],
            ['name' => 'Kulkas', 'description' => 'Hadiah kulkas rumah tangga.', 'quantity' => 3, 'sequence' => 3, 'nominal_per_poin' => 500_000],
            ['name' => 'Sepeda', 'description' => 'Hadiah sepeda untuk peserta.', 'quantity' => 5, 'sequence' => 4, 'nominal_per_poin' => 1_000_000],
        ];

        foreach ($prizes as $prizeData) {
            $prize = Prize::updateOrCreate(
                ['raffle_period_id' => $period->id, 'name' => $prizeData['name']],
                $prizeData + [
                    'status' => 'active',
                    'active_for_exchange' => true,
                ]
            );

        }

        $paymentTypes = [
            ['code' => 'TUNAI', 'name' => 'Tunai', 'description' => 'Pembayaran tunai.'],
            ['code' => 'KARTU_MEGA', 'name' => 'Kartu Kredit Bank Mega', 'description' => 'Pembayaran dengan kartu kredit Bank Mega.'],
            ['code' => 'KARTU_MALL', 'name' => 'Kartu Mall', 'description' => 'Pembayaran dengan kartu mall.'],
            ['code' => 'DEBIT_LAIN', 'name' => 'Debit Bank Lain', 'description' => 'Pembayaran dengan kartu debit bank lain.'],
        ];

        foreach ($paymentTypes as $paymentTypeData) {
            $paymentType = PaymentType::updateOrCreate(
                ['code' => $paymentTypeData['code']],
                $paymentTypeData + ['is_active' => true]
            );

            BonusPointRule::updateOrCreate(
                ['raffle_period_id' => $period->id, 'payment_type_id' => $paymentType->id],
                [
                    'bonus_poin' => match ($paymentType->code) {
                        'KARTU_MEGA' => 2,
                        'KARTU_MALL' => 1,
                        default => 0,
                    },
                    'is_active' => true,
                ]
            );
        }
    }
}
