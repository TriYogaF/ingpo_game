<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //All post
    public function index()
    {
        return response([
            'posts' => Post::orderBy('created_at', 'desc')->with('user:id,name,image')->withCount('comments', 'likes')
            ->with('likes', function($like){
                return $like->where('user_id', auth()->user()->id)
                ->select('id', 'user_id', 'post_id')->get();
            })
            ->get()
        ],200);
    }

    //Single post
    public function show($id)
    {
        return response([
            'posts' => Post::where('id', $id)->withCount('comments', 'likes')->get()
        ],200);
    }

    //Create post
    public function store(Request $request)
    {
        //validate
        $attrs = $request->validate([
            'body' => 'required|string',
            // 'title' => 'required|string'
        ]);

        $image = $this->saveImage($request->image, 'posts');

        $post = Post::create([
            'body' => $attrs['body'],
            // 'title' => $attrs['title'],
            'user_id' => auth()->user()->id,
            'image' => $image
        ]);


        return response([
            'message' => 'Post created',
            'post' => $post
        ], 200);
    }

    //update post
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if(!$post)
        {
            return response([
                'message' => 'Post not found'
            ], 403);
        }

        if($post->user_id != auth()->user()->id)
        {
            return response([
                'message' => 'Permission denied'
            ], 403);
        }

        //validate
        $attrs = $request->validate([
            'body' => 'required|string',
            // 'title' => 'required|string'
        ]);

        $post->update([
            // 'title' => $attrs['title'],
            'body' => $attrs['body']
        ]);

        return response([
            'message' => 'Post updated',
            'post' => $post
        ], 200);
    }

    //delete post
    public function destroy($id)
    {
        $post = Post::find($id);

        if(!$post)
        {
            return response([
                'message' => 'Post not found'
            ], 403);
        }

        if($post->user_id != auth()->user()->id)
        {
            return response([
                'message' => 'Permission denied'
            ], 403);
        }

        $post->comments()->delete();
        $post->likes()->delete();
        $post->delete();

        return response([
            'message' => 'Post deleted',
        ], 200);
    }
}
