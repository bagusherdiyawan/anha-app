<?php

namespace App\Http\Controllers\Api\V1\user;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use App\Models\PushNotification;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = PushNotification::where(
            'user_id',
            auth()->id()
        )
        ->orWhere('user_id',0)
        ->latest()
        ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function show($id)
    {
        $notification = PushNotification::where(function ($q) {
            $q->where('user_id', auth()->id())
              ->orWhere('user_id', 0);
        })
        ->where('id', $id)
        ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        if (!$notification->is_read && $notification->user_id !== 0) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    public function markAsRead($id)
    {
        $notification = PushNotification::where(function ($q) {
            $q->where('user_id', auth()->id())
              ->orWhere('user_id', 0);
        })
        ->where('id', $id)
        ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        if ($notification->user_id !== 0) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi telah dibaca'
        ]);
    }

    public function markAllAsRead()
    {
        PushNotification::where(
            'user_id',
            auth()->id()
        )
        ->where('is_read', false)
        ->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah dibaca'
        ]);
    }

    public function unreadCount()
    {
        $count = PushNotification::where(function ($q) {
            $q->where('user_id', auth()->id())
              ->orWhere('user_id', 0);
        })
        ->where('is_read', false)
        ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }
}
