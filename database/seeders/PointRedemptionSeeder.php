<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\PaymentType;
use App\Models\PointRedemption;
use App\Models\Prize;
use App\Models\Purchase;
use App\Models\RafflePeriod;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PointCalculationService;
use App\Services\PointRedemptionService;
use Illuminate\Database\Seeder;

class PointRedemptionSeeder extends Seeder
{
    public function run(): void
    {
        $period = RafflePeriod::where('code', 'MALL-2026')->firstOrFail();
        $customers = Customer::query()->orderBy('id')->limit(20)->get();
        $tenants = Tenant::query()->orderBy('id')->limit(5)->get();
        $paymentTypes = PaymentType::query()->whereIn('code', ['TUNAI', 'KARTU_MEGA', 'KARTU_MALL'])
            ->get()
            ->keyBy('code');
        $cs = User::where('email', 'customerservice@example.com')->firstOrFail();
        $prizes = Prize::query()->where('raffle_period_id', $period->id)->orderBy('sequence')->get();
        $service = new PointRedemptionService(new PointCalculationService);

        foreach ($prizes as $prizeIndex => $prize) {
            foreach ($customers->slice($prizeIndex * 5, 5) as $customerIndex => $customer) {
                $receiptNumber = sprintf('DEMO-%s-%02d-%02d', $period->code, $prizeIndex + 1, $customerIndex + 1);
                $existingRedemption = PointRedemption::whereHas('purchase', fn ($query) => $query->where('receipt_number', $receiptNumber))->first();

                if ($existingRedemption) {
                    continue;
                }

                $paymentType = match ($customerIndex % 3) {
                    1 => $paymentTypes->get('KARTU_MEGA'),
                    2 => $paymentTypes->get('KARTU_MALL'),
                    default => $paymentTypes->get('TUNAI'),
                };
                $amount = $prize->nominal_per_poin * (3 + $customerIndex) + ($customerIndex * 50_000);
                $purchase = Purchase::updateOrCreate(
                    [
                        'raffle_period_id' => $period->id,
                        'tenant_id' => $tenants[$customerIndex % $tenants->count()]->id,
                        'receipt_number' => $receiptNumber,
                    ],
                    [
                        'customer_id' => $customer->id,
                        'entered_by' => $cs->id,
                        'purchased_at' => now()->subDays(5 - $customerIndex),
                        'amount' => $amount,
                        'payment_type_id' => $paymentType?->id,
                        'exchange_status' => 'belum',
                        'notes' => 'Data demo untuk pengujian drawing berbasis poin.',
                    ]
                );

                $service->redeem($customer, $purchase, $prize, $cs->id, 'Seed demo redemption.');
            }
        }
    }
}
