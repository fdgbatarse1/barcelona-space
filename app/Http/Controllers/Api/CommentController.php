<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a place with sorting and pagination.
     */
    public function index(Request $request, Place $place): AnonymousResourceCollection
    {
        $query = $place->comments()->with('user');

        // Sorting (prefix with - for descending)
        $sortField = $request->query('sort', '-created_at');
        $sortDirection = 'asc';

        if (str_starts_with($sortField, '-')) {
            $sortDirection = 'desc';
            $sortField = substr($sortField, 1);
        }

        // Only allow sorting by specific fields
        $allowedSorts = ['created_at', 'updated_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min((int) $request->query('per_page', 15), 100);

        return CommentResource::collection($query->paginate($perPage));
    }

    /**
     * Store a newly created comment for a place.
     */
    public function store(StoreCommentRequest $request, Place $place): JsonResponse
    {
        $comment = $place->comments()->create([
            'text' => $request->validated('text'),
            'user_id' => $request->user()->id,
        ]);

        $comment->load('user');

        return response()->json([
            'message' => 'Comment created successfully.',
            'data' => new CommentResource($comment),
        ], 201);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // Check ownership
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to update this comment.',
            ], 403);
        }

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:1000'],
        ]);

        $comment->update($validated);
        $comment->load('user');

        return response()->json([
            'message' => 'Comment updated successfully.',
            'data' => new CommentResource($comment),
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        // Check ownership
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this comment.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ], 200);
    }
}

