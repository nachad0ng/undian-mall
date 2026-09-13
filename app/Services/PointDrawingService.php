<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CustomerPointBalance;
use App\Models\Drawing;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointDrawingService
{
    public function preview(Prize $prize): array
    {
        $prize->loadMissing('rafflePeriod');
        $balances = $this->eligibleBalances($prize);
        $poolCount = (int) $balances->sum('total_poin');
        $reason = null;

        if ($prize->status !== 'active') {
            $reason = 'Hadiah tidak aktif untuk proses pengundian.';
        } elseif ($prize->rafflePeriod->status !== 'active'
            || $prize->rafflePeriod->drawing_status === 'completed') {
            $reason = 'Periode tidak aktif untuk proses pengundian.';
        } elseif ($prize->quantity < 1) {
            $reason = 'Hadiah belum memiliki kuota pemenang.';
        } elseif ($poolCount < $prize->quantity || $balances->count() < $prize->quantity) {
            $reason = 'Jumlah poin peserta tidak cukup untuk kuota hadiah.';
        } elseif (Drawing::query()
            ->where('prize_id', $prize->id)
            ->whereIn('status', ['in_progress', 'completed'])
            ->exists()) {
            $reason = 'Hadiah ini sudah pernah diundi.';
        }

        return [
            'prize_id' => $prize->id,
            'period_id' => $prize->raffle_period_id,
            'quantity' => $prize->quantity,
            'pool_count' => $poolCount,
            'eligible_customers' => $balances->count(),
            'can_draw' => $reason === null,
            'reason' => $reason,
        ];
    }

    public function draw(Prize $prize, User $executor): Drawing
    {
        return DB::transaction(function () use ($prize, $executor) {
            $prize = Prize::query()
                ->whereKey($prize->id)
                ->lockForUpdate()
                ->firstOrFail();
            $prize->load('rafflePeriod');

            if ($prize->status !== 'active') {
                throw new \RuntimeException('Hadiah tidak aktif untuk proses pengundian.');
            }

            if ($prize->rafflePeriod->status !== 'active'
                || $prize->rafflePeriod->drawing_status === 'completed') {
                throw new \RuntimeException('Periode tidak aktif untuk proses pengundian.');
            }

            if ($prize->quantity < 1) {
                throw new \RuntimeException('Hadiah belum memiliki kuota pemenang.');
            }

            if (Drawing::query()
                ->where('prize_id', $prize->id)
                ->whereIn('status', ['in_progress', 'completed'])
                ->exists()) {
                throw new \RuntimeException('Hadiah ini sudah pernah diundi.');
            }

            $balances = $this->eligibleBalances($prize)
                ->lockForUpdate()
                ->get();

            $poolCount = (int) $balances->sum('total_poin');

            if ($poolCount < $prize->quantity || $balances->count() < $prize->quantity) {
                throw new \RuntimeException('Jumlah poin peserta tidak cukup untuk kuota hadiah.');
            }

            $selectedCustomerIds = $this->selectWeightedCustomers($balances, $prize->quantity);
            $drawing = Drawing::create([
                'raffle_period_id' => $prize->raffle_period_id,
                'prize_id' => $prize->id,
                'executed_by' => $executor->id,
                'executed_at' => now(),
                'status' => 'completed',
                'metadata' => [
                    'pool_count' => $poolCount,
                    'eligible_customers' => $balances->count(),
                    'winner_count' => count($selectedCustomerIds),
                    'selection_method' => 'weighted_customer_points_without_replacement',
                ],
            ]);

            foreach ($selectedCustomerIds as $customerId) {
                $drawing->winners()->create([
                    'raffle_period_id' => $prize->raffle_period_id,
                    'prize_id' => $prize->id,
                    'customer_id' => $customerId,
                    'won_at' => now(),
                    'is_published' => false,
                ]);
            }

            AuditLog::create([
                'user_id' => $executor->id,
                'action' => 'drawing.completed',
                'auditable_type' => Drawing::class,
                'auditable_id' => $drawing->id,
                'metadata' => [
                    'prize_id' => $prize->id,
                    'winner_ids' => $selectedCustomerIds,
                    'pool_count' => $poolCount,
                ],
            ]);

            return $drawing->load('winners.customer');
        });
    }

    private function selectWeightedCustomers($balances, int $winnerCount): array
    {
        $selectedCustomerIds = [];
        $remaining = $balances->keyBy('customer_id');

        for ($winner = 0; $winner < $winnerCount; $winner++) {
            $totalWeight = (int) $remaining->sum('total_poin');
            $target = random_int(1, $totalWeight);
            $cumulative = 0;

            foreach ($remaining as $customerId => $balance) {
                $cumulative += $balance->total_poin;
                if ($target <= $cumulative) {
                    $selectedCustomerIds[] = (int) $customerId;
                    $remaining->forget($customerId);
                    break;
                }
            }
        }

        return $selectedCustomerIds;
    }

    private function eligibleBalances(Prize $prize)
    {
        return CustomerPointBalance::query()
            ->where('raffle_period_id', $prize->raffle_period_id)
            ->where('prize_id', $prize->id)
            ->where('total_poin', '>', 0);
    }
}
