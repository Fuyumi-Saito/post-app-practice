<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reply;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $post->replies()->create([
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('posts.show', $post);
    }

    public function destroy(Reply $reply)
    {
        $this->authorize('delete', $reply);

        $post = $reply->post;

        $reply->delete();

        return redirect()->route('posts.show', $post);
    }
}
