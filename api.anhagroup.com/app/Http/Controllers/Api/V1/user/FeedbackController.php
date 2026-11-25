<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $feedbacks = $request
            ->user()
            ->feedbacks()
            ->with('replies.admin')
            ->latest()
            ->paginate(15);

        return FeedbackResource::collection($feedbacks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|in:product,delivery,service,app,other',
            'content' => 'required|string|min:20',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('feedbacks', 'public');
            }
        }

        $feedback = $request->user()->feedbacks()->create([
            'category' => $request->category,
            'content' => $request->content,
            'photo_paths' => $photoPaths,
        ]);

        return response()->json([
            'message' => 'Kritik & Saran berhasil dikirim.',
            'data' => new FeedbackResource($feedback),
        ], 201);
    }

    public function show(Request $request, Feedback $feedback)
    {
        if ($feedback->user_id !== $request->user()->id)
            abort(403);

        if ($feedback->status === 'submitted') {
            $feedback->update(['status' => 'read']);
        }

        $feedback->load('replies.admin');

        return response()->json(['data' => new FeedbackResource($feedback)]);
    }
}
