<?php

namespace App\Services;

use App\Models\BonusPointRule;
use App\Models\Prize;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use Carbon\CarbonInterface;

class PointCalculationService
{
    /**
     * Hitung poin untuk satu struk berdasarkan hadiah yang dipilih.
     *
     * Formula: total_poin = FLOOR(total_belanja / nominal_per_poin) + bonus_poin
     * Sisa nominal yang tidak genap kelipatan = hangus (tidak di-carry-over).
     */
    public function calculatePointsForPurchase(
        Purchase $purchase,
        Prize $prize,
        ?RafflePeriod $period = null
    ): array {
        $period = $period ?? $purchase->rafflePeriod;

        $nominalPerPoin = $this->getNominalPerPoinForPrize($prize);
        $pointsFromAmount = $this->calculatePointsFromAmount($purchase->amount, $nominalPerPoin);
        $bonusPoints = $this->calculateBonusPoints($purchase, $period);

        return [
            'nominal_per_poin' => $nominalPerPoin,
            'points_from_amount' => $pointsFromAmount,
            'bonus_points' => $bonusPoints,
            'total_points' => $pointsFromAmount + $bonusPoints,
            'unused_remainder' => $purchase->amount % $nominalPerPoin, // hangus
        ];
    }

    /**
     * Dapatkan nominal poin dari konfigurasi hadiah pada periode tersebut.
     */
    private function getNominalPerPoinForPrize(Prize $prize): int
    {
        if ($prize->nominal_per_poin !== null && $prize->nominal_per_poin > 0) {
            return $prize->nominal_per_poin;
        }

        throw new \RuntimeException("Hadiah {$prize->name} tidak memiliki rule poin yang aktif.");
    }

    /**
     * Hitung poin dari nominal belanja.
     * FLOOR(amount / nominal_per_poin).
     */
    private function calculatePointsFromAmount(int $amount, int $nominalPerPoin): int
    {
        if ($nominalPerPoin <= 0) {
            return 0;
        }

        return intdiv($amount, $nominalPerPoin);
    }

    /**
     * Hitung bonus poin dari tipe pembayaran.
     */
    private function calculateBonusPoints(Purchase $purchase, RafflePeriod $period): int
    {
        if (! $purchase->payment_type_id) {
            return 0;
        }

        $rule = BonusPointRule::findActiveForPeriodAndPaymentType(
            $period->id,
            $purchase->payment_type_id
        );

        return $rule ? $rule->bonus_poin : 0;
    }

    /**
     * Validasi apakah struk boleh ditukar.
     * Cek: status_tukar, rentang tanggal belanja, rentang tanggal tukar.
     */
    public function validatePurchaseForRedemption(
        Purchase $purchase,
        RafflePeriod $period,
        ?CarbonInterface $now = null
    ): array {
        $errors = [];

        // 1. Struk belum ditukar
        if ($purchase->isAlreadyRedeemed()) {
            $errors[] = 'Struk sudah pernah ditukar poinnya.';
        }

        // 2. Tanggal belanja masuk rentang periode
        if (! $now) {
            $now = now();
        }
        if (! $purchase->purchased_at->between($period->start_at, $period->end_at)) {
            $errors[] = 'Tanggal belanja tidak masuk rentang periode event.';
        }

        // 3. Tanggal tukar (now) masuk rentang exchange
        $exchangeStart = $period->exchange_start_at ?? $period->start_at;
        $exchangeEnd = $period->exchange_end_at ?? $period->end_at;
        if (! $now->between($exchangeStart, $exchangeEnd)) {
            $errors[] = 'Penukaran poin sudah di luar jadwal.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
