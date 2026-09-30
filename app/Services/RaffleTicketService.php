<?php

namespace App\Services;

use App\Models\PointRedemption;
use App\Models\Prize;
use App\Models\RaffleTicket;
use Illuminate\Support\Facades\DB;

class RaffleTicketService
{
    /**
     * Terbitkan N tiket (1 poin = 1 nomor) untuk satu redemption.
     * Sequence terpisah per hadiah, format zero-pad sesuai ticket_digits.
     *
     * @return RaffleTicket[]
     */
    public function issueForRedemption(PointRedemption $redemption, int $count): array
    {
        if ($count < 1) {
            throw new \RuntimeException('Nominal struk kurang untuk mendapatkan nomor undian.');
        }

        return DB::transaction(function () use ($redemption, $count) {
            /** @var Prize $prize */
            $prize = Prize::query()->whereKey($redemption->prize_id)->lockForUpdate()->firstOrFail();
            $digits = max(1, (int) ($prize->ticket_digits ?: 3));
            $capacity = (int) (10 ** $digits) - 1;

            if ($prize->ticket_counter + $count > $capacity) {
                throw new \RuntimeException(
                    "Kuota nomor undian hadiah {$prize->name} habis (maks {$capacity} untuk {$digits} digit)."
                );
            }

            $tickets = [];
            for ($i = 1; $i <= $count; $i++) {
                $sequence = $prize->ticket_counter + $i;
                $tickets[] = RaffleTicket::create([
                    'raffle_period_id' => $redemption->raffle_period_id,
                    'prize_id' => $prize->id,
                    'customer_id' => $redemption->customer_id,
                    'point_redemption_id' => $redemption->id,
                    'purchase_id' => $redemption->purchase_id,
                    'sequence_number' => $sequence,
                    'ticket_number' => str_pad((string) $sequence, $digits, '0', STR_PAD_LEFT),
                ]);
            }

            $prize->increment('ticket_counter', $count);

            return $tickets;
        });
    }
}
