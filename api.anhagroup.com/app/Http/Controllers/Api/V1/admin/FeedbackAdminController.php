<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Feedback\FeedbackReplyResource;
use App\Http\Resources\Feedback\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackAdminController extends Controller
{
    /**
     * List feedback
     */
    public function index(Request $request)
    {
        $query = Feedback::with('user')
            ->latest();

        // filter category
        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->category
            );
        }

        // filter status
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        // search
        if ($request->filled('search')) {
            $query->where(
                'content',
                'like',
                '%' . $request->search . '%'
            );
        }

        $feedbacks = $query
            ->paginate($request->per_page ?? 10)
            ->appends($request->query());

        return FeedbackResource::collection($feedbacks);
    }

    /**
     * Detail feedback
     */
    public function show(string $id): JsonResponse
    {
        $feedback = Feedback::with([
            'user',
            'replies.admin'
        ])->find($id);

        if (!$feedback) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback tidak ditemukan'
            ], 404);
        }

        // auto read
        if ($feedback->status === 'submitted') {
            $feedback->update([
                'status' => 'read'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail feedback',
            'data' => new FeedbackResource(
                $feedback->fresh([
                    'user',
                    'replies.admin'
                ])
            )
        ]);
    }

    /**
     * Reply feedback
     */
    public function reply(Request $request, string $id): JsonResponse
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'content' => 'required|string|min:1',
        ]);

        $feedback->replies()->create([
            'admin_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        $feedback->update([
            'status' => 'replied'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil dikirim',
            'data' => new FeedbackResource(
                $feedback->fresh([
                    'user',
                    'replies.admin'
                ])
            )
        ]);
    }

    /**
     * Update status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'status' =>
                'required|in:submitted,read,replied,archived',
        ]);

        $feedback->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status feedback berhasil diperbarui',
            'data' => new FeedbackResource($feedback)
        ]);
    }

    /**
     * Archive feedback
     */
    public function archive(string $id): JsonResponse
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback tidak ditemukan'
            ], 404);
        }

        $feedback->update([
            'status' => 'archived'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback berhasil diarsipkan'
        ]);
    }

    /**
     * Delete feedback
     */
    public function destroy(string $id): JsonResponse
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback tidak ditemukan'
            ], 404);
        }

        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback berhasil dihapus'
        ]);
    }
}
