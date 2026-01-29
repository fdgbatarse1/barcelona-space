<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsToSentry;
use App\Models\Comment;
use App\Models\Place;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use LogsToSentry;

    public function store(Request $request, Place $place)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $comment = $place->comments()->create([
            'text' => $validated['text'],
            'user_id' => $request->user()->id,
        ]);

        cache()->forget('place_' . $place->id . '_comments_page_1');

        $context = ['comment_id' => $comment->id, 'place_id' => $place->id, 'user_id' => $request->user()->id];
        $this->addBreadcrumb('comment.created', 'Comment created', $context);
        $this->logAction('info', 'Comment created', $context);

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

        $context = ['comment_id' => $comment->id, 'place_id' => $comment->place_id, 'user_id' => auth()->id()];
        $this->addBreadcrumb('comment.updated', 'Comment updated', $context);
        $this->logAction('info', 'Comment updated', $context);

        return back()->with('success', 'Comment updated successfully.');
    }

    public function destroy(Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);

        $context = ['comment_id' => $comment->id, 'place_id' => $comment->place_id, 'user_id' => auth()->id()];
        $this->addBreadcrumb('comment.deleted', 'Comment deleted', $context);
        $this->logAction('info', 'Comment deleted', $context);

        $comment->delete();

        cache()->forget('place_' . $comment->place_id . '_comments_page_1');

        return back()->with('success', 'Comment deleted successfully.');
    }
}
