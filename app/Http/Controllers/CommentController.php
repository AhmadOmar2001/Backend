<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    //Add Comment Function
    public function addComment(Post $post, CommentRequest $commentRequest)
    {
        $user = Auth::guard('user')->user();

        Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => $commentRequest->comment,
        ]);

        return success(null, 'your comment added successfully', 201);
    }

    //Delete Comment Function
    public function deleteComment(Comment $comment)
    {
        $comment->delete();

        return success(null, 'this comment deleted successfully');
    }

    //Get Post Comments Function
    public function getPostComments(Post $post)
    {
        $comments = $post->comments()->with('user')->orderBy('id', 'desc')->get();

        return success($comments, null);
    }
}