<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Drawing;
use App\Models\Prize;
use App\Models\RaffleTicket;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Support\Facades\DB;

class ManualDrawingService
{
    public function preview(Prize $prize): array
    {
        $prize->loadMissing('rafflePeriod');
        $ticketCount = RaffleTicket::query()->where('prize_id', $prize->id)->count();
        $winnerCount = Winner::query()->where('prize_id', $prize->id)->count();
        $remaining = max(0, $prize->quantity - $winnerCount);
        $reason = $this->blockReason($prize, $ticketCount, $winnerCount);

        return [
            'prize_id' => $prize->id,
            'period_id' => $prize->raffle_period_id,
            'ticket_digits' => (int) ($prize->ticket_digits ?: 3),
            'ticket_count' => $ticketCount,
            'winner_count' => $winnerCount,
            'quantity' => $prize->quantity,
            'remaining' => $remaining,
            'can_draw' => $reason === null,
            'reason' => $reason,
        ];
    }

    /**
     * Undian manual bola: nomor pemenang (misal 010) dicocokkan ke pemilik tiket.
     */
    public function draw(Prize $prize, string $winningNumber, User $executor): Drawing
    {
        return DB::transaction(function () use ($prize, $winningNumber, $executor) {
            $prize = Prize::query()->whereKey($prize->id)->lockForUpdate()->firstOrFail();
            $prize->load('rafflePeriod');

            $digits = max(1, (int) ($prize->ticket_digits ?: 3));
            $winningNumber = trim($winningNumber);

            if (! preg_match('/^\d{1,20}$/', $winningNumber) || strlen($winningNumber) !== $digits) {
                throw new \RuntimeException("Nomor pemenang harus {$digits} digit angka (0-9).");
            }

            $ticketCount = RaffleTicket::query()->where('prize_id', $prize->id)->count();
            $winnerCount = Winner::query()->where('prize_id', $prize->id)->count();
            $reason = $this->blockReason($prize, $ticketCount, $winnerCount);
            if ($reason !== null) {
                throw new \RuntimeException($reason);
            }

            $ticket = RaffleTicket::query()
                ->where('prize_id', $prize->id)
                ->where('ticket_number', $winningNumber)
                ->lockForUpdate()
                ->first();

            if (! $ticket) {
                throw new \RuntimeException("Nomor {$winningNumber} tidak terdaftar untuk hadiah ini. Ambil ulang bola / cek nomor.");
            }

            if (Winner::query()->where('prize_id', $prize->id)->where('raffle_ticket_id', $ticket->id)->exists()) {
                throw new \RuntimeException("Nomor {$winningNumber} sudah menang sebelumnya.");
            }

            if (Winner::query()->where('prize_id', $prize->id)->where('customer_id', $ticket->customer_id)->exists()) {
                throw new \RuntimeException('Customer pemilik nomor ini sudah menang untuk hadiah yang sama.');
            }

            $drawing = Drawing::create([
                'raffle_period_id' => $prize->raffle_period_id,
                'prize_id' => $prize->id,
                'executed_by' => $executor->id,
                'executed_at' => now(),
                'status' => 'completed',
                'metadata' => [
                    'selection_method' => 'manual_ball',
                    'winning_number' => $winningNumber,
                    'ticket_id' => $ticket->id,
                    'ticket_digits' => $digits,
                ],
            ]);

            $drawing->winners()->create([
                'raffle_period_id' => $prize->raffle_period_id,
                'prize_id' => $prize->id,
                'customer_id' => $ticket->customer_id,
                'raffle_ticket_id' => $ticket->id,
                'winning_number' => $winningNumber,
                'won_at' => now(),
                'is_published' => false,
            ]);

            AuditLog::create([
                'user_id' => $executor->id,
                'action' => 'drawing.manual_completed',
                'auditable_type' => Drawing::class,
                'auditable_id' => $drawing->id,
                'metadata' => [
                    'prize_id' => $prize->id,
                    'winning_number' => $winningNumber,
                    'ticket_id' => $ticket->id,
                    'customer_id' => $ticket->customer_id,
                ],
            ]);

            return $drawing->load('winners.customer');
        });
    }

    private function blockReason(Prize $prize, int $ticketCount, int $winnerCount): ?string
    {
        if ($prize->status !== 'active') {
            return 'Hadiah tidak aktif untuk proses pengundian.';
        }

        if ($prize->rafflePeriod->status !== 'active' || $prize->rafflePeriod->drawing_status === 'completed') {
            return 'Periode tidak aktif untuk proses pengundian.';
        }

        if ($prize->quantity < 1) {
            return 'Hadiah belum memiliki kuota pemenang.';
        }

        if (Drawing::query()->where('prize_id', $prize->id)->whereJsonContains('metadata->selection_method', 'weighted_customer_points_without_replacement')->exists()) {
            return 'Hadiah ini sudah diundi otomatis. Mode manual tidak bisa dipakai bersamaan.';
        }

        if ($ticketCount < 1) {
            return 'Belum ada nomor undian yang diterbitkan untuk hadiah ini.';
        }

        if ($winnerCount >= $prize->quantity) {
            return 'Kuota pemenang hadiah ini sudah terpenuhi.';
        }

        return null;
    }
}
