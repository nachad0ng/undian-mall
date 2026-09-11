<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPointBalance;
use App\Models\PointRedemption;
use App\Models\Prize;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use Illuminate\Support\Facades\DB;

class PointRedemptionService
{
    public function __construct(
        private PointCalculationService $calculator
    ) {}

    /**
     * Proses penukaran poin: customer memilih hadiah, serahkan struk.
     * 1 struk = 1 hadiah. Struk akan dikunci setelah berhasil.
     */
    public function redeem(
        Customer $customer,
        Purchase $purchase,
        Prize $prize,
        ?int $csId = null,
        ?string $notes = null
    ): PointRedemption {
        return DB::transaction(function () use ($customer, $purchase, $prize, $csId, $notes) {
            $period = $purchase->rafflePeriod;

            // 1. Struk dan hadiah harus satu periode — cek ini paling pertama
            if ($prize->rafflePeriod->id !== $period->id) {
                throw new \RuntimeException('Hadiah dan struk harus dari periode yang sama.');
            }

            // 2. Validasi umum struk (status, rentang tanggal)
            $validation = $this->calculator->validatePurchaseForRedemption($purchase, $period);
            if (! $validation['valid']) {
                throw new \RuntimeException(implode(' ', $validation['errors']));
            }

            // 3. Hadiah harus bisa ditukar
            if (! $prize->canBeExchanged()) {
                throw new \RuntimeException('Hadiah tidak bisa dipakai untuk tukar poin.');
            }

            // Hitung poin
            $calc = $this->calculator->calculatePointsForPurchase($purchase, $prize, $period);

            // Simpan transaksi penukaran
            $redemption = PointRedemption::create([
                'customer_id' => $customer->id,
                'raffle_period_id' => $period->id,
                'prize_id' => $prize->id,
                'purchase_id' => $purchase->id,
                'cs_id' => $csId,
                'redeemed_at' => now(),
                'nominal_struk' => $purchase->amount,
                'total_poin_didapat' => $calc['total_points'],
                'status' => 'success',
                'notes' => $notes,
            ]);

            // Kunci struk
            $purchase->markAsRedeemed();

            // Update saldo poin customer
            $balance = CustomerPointBalance::findOrCreateForCustomerPeriodPrize(
                $customer->id,
                $period->id,
                $prize->id
            );
            $balance->addPoints($calc['total_points']);

            return $redemption;
        });
    }

    /**
     * Dapatkan daftar hadiah aktif untuk periode tertentu (untuk halaman pilihan hadiah customer).
     */
    public function getActivePrizesForPeriod(RafflePeriod $period): array
    {
        $prizes = $period->getActivePrizesForExchange();

        return $prizes->map(function (Prize $prize) {
            return [
                'id' => $prize->id,
                'name' => $prize->name,
                'description' => $prize->description,
                'gambar_url' => $prize->gambar_url,
                'kuota' => $prize->kuota,
                'nominal_per_poin' => $prize->nominal_per_poin,
                'sequence' => $prize->sequence,
                'can_exchange' => $prize->canBeExchanged(),
            ];
        })->toArray();
    }

    /**
     * Dapatkan saldo poin customer untuk semua hadiah di periode tertentu.
     */
    public function getCustomerPointBalances(Customer $customer, RafflePeriod $period): array
    {
        $balances = $customer->pointBalances()
            ->where('raffle_period_id', $period->id)
            ->with('prize')
            ->get();

        return $balances->map(function (CustomerPointBalance $balance) {
            return [
                'prize_id' => $balance->prize_id,
                'prize_name' => $balance->prize->name,
                'total_poin' => $balance->total_poin,
            ];
        })->toArray();
    }

    /**
     * Cek apakah struk udah pernah ditukar (double-check sebelum redemption).
     */
    public function isPurchaseAlreadyRedeemed(Purchase $purchase): bool
    {
        return PointRedemption::where('purchase_id', $purchase->id)->exists()
            || $purchase->status_tukar === 'sudah';
    }
}
