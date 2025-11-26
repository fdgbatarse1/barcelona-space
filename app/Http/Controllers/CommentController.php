<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Place;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Place $place)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $place->comments()->create([
            'text' => $validated['text'],
            'user_id' => $request->user()->id,
        ]);

        cache()->forget('place_' . $place->id . '_comments_page_1');

        return back()->with('success', 'Comment added successfully.');
    }

    public function update(Request $request, Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $comment->update($validated);

        cache()->forget('place_' . $comment->place_id . '_comments_page_1');

        return back()->with('success', 'Comment updated successfully.');
    }

    public function destroy(Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);

        $comment->delete();

        cache()->forget('place_' . $comment->place_id . '_comments_page_1');

        return back()->with('success', 'Comment deleted successfully.');
    }
}
