<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\RewardRedemption;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RewardRedemptionAdminController extends Controller
{
    public function __construct(private readonly LoyaltyService $loyaltyService)
    {
    }

    public function index(Request $request)
    {
        try {
            $limit = $request->get('limit', 15);
            $search = $request->get('search');
            $status = $request->get('status');

            $query = RewardRedemption::with(['user', 'reward'])
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('claim_code', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function($uq) use ($search) {
                          $uq->where('name', 'LIKE', "%{$search}%")
                             ->orWhere('email', 'LIKE', "%{$search}%");
                      });
                });
            }

            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            $redemptions = $query->paginate($limit);

            // modify image URL for reward
            $redemptions->getCollection()->transform(function ($redemption) {
                if ($redemption->reward && $redemption->reward->image) {
                    $redemption->reward->image_url = url('storage/' . $redemption->reward->image);
                }
                return $redemption;
            });

            return response()->json([
                'success' => true,
                'data' => $redemptions
            ]);
        } catch (\Exception $e) {
            Log::error('RewardRedemptionAdminController::index gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data klaim hadiah'
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:claimed,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $redemption = RewardRedemption::find($id);

            if (!$redemption) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data klaim tidak ditemukan'
                ], 404);
            }

            if ($redemption->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya klaim berstatus pending yang dapat diubah.'
                ], 400);
            }

            DB::beginTransaction();

            $redemption->status = $request->status;
            $redemption->confirmed_by = $request->user()->id;

            if ($request->status === 'claimed') {
                $redemption->claimed_at = Carbon::now();
            } elseif ($request->status === 'cancelled') {
                $this->loyaltyService->refundRedemption($redemption, 'pembatalan klaim hadiah');
            }

            $redemption->save();

            DB::commit();

            // Load relations for response
            $redemption->load(['user', 'reward']);

            return response()->json([
                'success' => true,
                'message' => 'Status klaim hadiah berhasil diperbarui',
                'data' => $redemption
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RewardRedemptionAdminController::updateStatus gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status'
            ], 500);
        }
    }
}
