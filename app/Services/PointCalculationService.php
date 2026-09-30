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
     * Formula add: total_poin = FLOOR(total_belanja / nominal_per_poin) + bonus_poin
     * Formula multiply: total_poin = FLOOR(FLOOR(total_belanja / nominal_per_poin) * multiplier)
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
        $bonus = $this->calculateBonus($purchase, $period, $pointsFromAmount);

        return [
            'nominal_per_poin' => $nominalPerPoin,
            'points_from_amount' => $pointsFromAmount,
            'bonus_points' => $bonus['bonus_points'],
            'bonus_mode' => $bonus['mode'],
            'bonus_multiplier' => $bonus['multiplier'],
            'bonus_label' => $bonus['label'],
            'total_points' => $bonus['total_points'],
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
     * Hitung bonus dari tipe pembayaran. Kembalikan rincian mode agar bisa
     * ditampilkan di preview dan disimpan sebagai snapshot historis.
     *
     * @return array{bonus_points: int, total_points: int, mode: ?string, multiplier: ?float, label: ?string, rule_id: ?int}
     */
    public function calculateBonus(Purchase $purchase, RafflePeriod $period, int $pointsFromAmount): array
    {
        $none = [
            'bonus_points' => 0,
            'total_points' => $pointsFromAmount,
            'mode' => null,
            'multiplier' => null,
            'label' => null,
            'rule_id' => null,
        ];

        if (! $purchase->payment_type_id) {
            return $none;
        }

        $rule = BonusPointRule::findActiveForPeriodAndPaymentType(
            $period->id,
            $purchase->payment_type_id
        );

        if (! $rule) {
            return $none;
        }

        $applied = $rule->applyToBasePoints($pointsFromAmount);

        return [
            'bonus_points' => $applied['bonus'],
            'total_points' => $applied['total'],
            'mode' => $rule->mode,
            'multiplier' => $rule->isMultiply() ? (float) $rule->multiplier : null,
            'label' => $rule->describe(),
            'rule_id' => $rule->id,
        ];
    }

    /**
     * Validasi apakah struk boleh ditukar.
     * Cek: exchange_status, rentang tanggal belanja, rentang tanggal tukar.
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
