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

        return back()->with('success', 'Comment added successfully.');
    }

}
