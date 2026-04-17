<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Post;
use PhpParser\Node\Scalar\String_;

class PostController extends Controller
{
    public function index(Request $req)
    {
        $data = Post::all();

        return response()->json([
            'status' => true,
            'message' => 'All Posts',
            'data' => $data
        ]);
    }

    public function store(Request $req)
    {
        $validate = Validator::make(
            $req->all(),
            [
                'title' => 'required',
                'des' => 'required',
                'image' => 'required|image'
            ]
        );

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'please enter valid information',
                'error' => $validate->errors()->all()
            ], 401);
        }

        if (!$req->hasFile('image')) {
            return response()->json([
                'status' => false,
                'message' => 'Image not found'
            ], 400);
        }

        $img = $req->file('image');
        $imgname = time() . '.' . $img->getClientOriginalExtension();
        $img->move(public_path('uploads'), $imgname);

        $post = Post::create([
            'title' => $req->title,
            'des' => $req->des,
            'image' => $imgname
        ]);

        return response()->json([
            'status' => true,
            'message' => 'post data stored successfully',
            'post' => $post,
        ], 201);
    }

    public function show(String $id)
    {
        $data = Post::select(
            'id',
            'title',
            'des',
            'image'
        )->where(['id' => $id])->get();

        return response()->json([
            'status' => true,
            'message' => 'Your post',
            'post' => $data,
        ], 200);
    }

    public function update(Request $req, String $id)
    {
        $validate = validator::make(
            $req->all(),
            [
                'title' => 'required',
                'des' => 'required',
                'image' => 'required'
            ]

        );

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'plese enter valid information',
                'error' => $validate->errors()->all()
            ], 401);
        }

        $postImage = Post::select('id', 'image')->where(['id' => $id])->get();

        if ($req->image != '') {
            $path = public_path() . '/uploads';
            if ($postImage[0]->image != '' && $postImage[0]->image != null) {
                $old_file = $path . $postImage[0]->image;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            $img = $req->file('image');

            $imgname = time() . '.' . $img->getClientOriginalExtension();

            $img->move(public_path('uploads'), $imgname);
        } else {
            $imgname = $postImage->image;
        }



        $post = Post::where(['id' => $id])->update([
            'title' => $req->title,
            'des' => $req->des,
            'image' => $imgname
        ]);

        return response()->json([
            'status' => true,
            'message' => 'post updated successfuly',
            'post' => $post,
        ], 200);
    }

    public function destroy(String $id)
    {
        $image = Post::select('image')->where('id', $id)->get();
        $filepath = public_path() . '/uploads/' . $image[0]['image'];
        unlink($filepath);
        $post = Post::where('id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'post delete successfuly',
            'post' => $post,
        ], 200);
    }
}
