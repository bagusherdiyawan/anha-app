<?php

namespace App\Console\Commands;

use App\Models\RewardRedemption;
use App\Services\LoyaltyService;
use Illuminate\Console\Command;

class ExpireRewardRedemptions extends Command
{
    protected $signature = 'rewards:expire';

    protected $description = 'Kedaluwarsakan klaim reward pending yang sudah lewat expires_at dan refund poin+stok';

    public function handle(LoyaltyService $loyaltyService): int
    {
        $expired = RewardRedemption::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $redemption) {
            $loyaltyService->refundRedemption($redemption, 'klaim hadiah kedaluwarsa');
            $redemption->status = 'expired';
            $redemption->save();
        }

        $this->info("Kedaluwarsakan {$expired->count()} klaim reward.");

        return self::SUCCESS;
    }
}
