<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\Loyalty\PointTransactionResource;
use App\Http\Resources\Loyalty\RedemptionResource;
use App\Http\Resources\Loyalty\RewardResource;
use App\Http\Resources\Loyalty\VoucherResource;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Services\LoyaltyService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $pointValue = (int) \App\Models\SystemConfig::get('points_value_in_rupiah', 500);

        $stats = $user->pointTransactions()
            ->selectRaw("
                COALESCE(SUM(amount), 0) as balance,
                COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) as total_earned,
                COALESCE(SUM(CASE WHEN amount < 0 AND type != 'expired' THEN -amount ELSE 0 END), 0) as total_spent,
                COALESCE(SUM(CASE WHEN amount < 0 AND type = 'expired' THEN -amount ELSE 0 END), 0) as expired
            ")
            ->first();

        $balance = (int) ($stats->balance ?? 0);

        return response()->json([
            'data' => [
                'points_balance' => $balance,
                'total_earned' => (int) ($stats->total_earned ?? 0),
                'total_spent' => (int) ($stats->total_spent ?? 0),
                'expired' => (int) ($stats->expired ?? 0),
                'points_value' => $balance * $pointValue,
                'points_value_formatted' => 'Rp ' . number_format($balance * $pointValue),
            ],
        ]);
    }

    public function transactions(Request $request): AnonymousResourceCollection
    {
        $transactions = $request
            ->user()
            ->pointTransactions()
            ->latest()
            ->paginate(20);

        return PointTransactionResource::collection($transactions);
    }

    public function rewards(Request $request): AnonymousResourceCollection
    {
        $rewards = Reward::query()
            ->with('barang')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('points_required')
            ->get();

        return RewardResource::collection($rewards);
    }

    public function redeem(Request $request, Reward $reward): JsonResponse
    {
        try {
            $redemption = $this->loyaltyService->redeem($request->user(), $reward);

            return response()->json([
                'success' => true,
                'message' => 'Penukaran berhasil.',
                'data' => new RedemptionResource($redemption),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function redemptions(Request $request): AnonymousResourceCollection
    {
        $query = $request
            ->user()
            ->rewardRedemptions()
            ->with('reward')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return RedemptionResource::collection($query->paginate(20));
    }

    public function redemptionDetail(Request $request, RewardRedemption $redemption): JsonResponse
    {
        if ($redemption->user_id !== $request->user()->id) {
            abort(403);
        }

        $redemption->load('reward');

        return response()->json(['data' => new RedemptionResource($redemption)]);
    }

    public function vouchers(Request $request): AnonymousResourceCollection
    {
        $vouchers = $request
            ->user()
            ->vouchers()
            ->where('is_used', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        return VoucherResource::collection($vouchers);
    }
}
