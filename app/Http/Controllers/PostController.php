<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditPostRequest;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    //Create Post Function
    public function createPost(PostRequest $postRequest)
    {
        $user = Auth::guard('user')->user();
        if ($postRequest->file('image')) {
            $path = $postRequest->file('image')->storePublicly('PostsImages', 'public');
        }
        Post::create([
            'user_id' => $user->id,
            'description' => $postRequest->description,
            'image' => 'storage/' . $path,
        ]);

        return success(null, 'your post created successfully', 201);
    }

    //Edit Post Function
    public function editPost(Post $post, EditPostRequest $editPostRequest)
    {
        $path = null;
        if ($editPostRequest->file('image')) {
            if (File::exists($post->image)) {
                File::delete($post->image);
            }
            $path = $editPostRequest->file('image')->storePublicly('PostsImages', 'public');
        }

        $post->update([
            'description' => $editPostRequest->description,
            'image' => $path == null ? $post->image : 'storage/' . $path
        ]);

        return success(null, 'post updated successfully');
    }

    //Delete Post Function
    public function deletePost(Post $post)
    {
        if (File::exists($post->image)) {
            File::delete($post->image);
        }

        $post->delete();
        return success(null, 'post deleted successfully');
    }

    //Get Posts Function
    public function getPosts(Request $request)
    {
        $user = Auth::guard('user')->user();
        if ($request->filter == 'mine') {
            $posts = $user->posts()->with('user', 'comments.user')->orderBy('id', 'desc')->get();
        } else {
            $posts = Post::orderBy('id', 'desc')->with('user')->get();
        }

        return success($posts, null);
    }
}
