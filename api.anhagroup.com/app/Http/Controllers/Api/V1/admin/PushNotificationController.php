<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PushNotificationController extends Controller
{

    public function index(Request $request)
    {
        $query = PushNotification::query();

        $search = $request->q ?? $request->search;
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->is_read);
        }

        $perPage = $request->input('per_page', $request->input('limit', 10));
        if ($perPage === 'all') {
            $perPage = 1000;
        }

        $notifications = $query->latest('id')->paginate((int) $perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'category' => 'required|in:order,shipping,points,voucher,feedback,promo,system',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array',
            'action_url' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {

            $validated['image'] = $request
            ->file('image')
            ->store('photos_notifications', 'public');
        }

        $validated['action_url'] = $validated['action_url'] ?? '';
        $notification = PushNotification::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dibuat',
            'data' => $notification
        ], 201);
    }

    public function show($id)
    {
        $notification = PushNotification::with('user')->findOrFail($id);

        $notification->image_url = $notification->image
        ? asset('storage/'.$notification->image)
        : null;

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    public function update(Request $request, $id)
    {
        $notification = PushNotification::findOrFail($id);

        $validated = $request->validate([
            'category' => 'sometimes|in:order,shipping,points,voucher,feedback,promo,system',
            'title' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'data' => 'nullable|array',
            'action_url' => 'nullable|string|max:500',
            'is_read' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {

            if ($notification->image) {
                Storage::disk('public')
                ->delete($notification->image);
            }

            $validated['image'] = $request
            ->file('image')
            ->store('photos_notifications', 'public');
        }

        if (
            isset($validated['is_read']) &&
            $validated['is_read'] == true &&
            !$notification->is_read
        ) {
            $validated['read_at'] = now();
        }

        $notification->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil diupdate',
            'data' => $notification
        ]);
    }

    public function destroy($id)
    {
        $notification = PushNotification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        if ($notification->image) {
            Storage::disk('public')
            ->delete($notification->image);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }
}