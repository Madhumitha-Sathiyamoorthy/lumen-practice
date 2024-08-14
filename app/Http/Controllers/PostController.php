<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\Users;
use App\Models\Likes;
use App\Models\Following;

use App\Models\SubComments;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;

use Carbon\Carbon;

class PostController extends Controller
{
    /**use Carbon\Carbon;
     * Create a new controller instance.
     *
     * @return void
     */
    public function commentOnPost(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'userId' => 'required',
                'postId' => 'required',
                'comment' => 'required',
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }

            $comment = new Comments;
            $comment->postId = $request->postId;
            $comment->userId = $request->userId;
            $comment->comment = $request->comment;
            $comment->created_at = Carbon::now()->toDateTimeString();
            $comment->updated_at = Carbon::now()->toDateTimeString();
            $comment->save();
            $response['Status'] = 'success';
            $response['Message'] = 'Comment posted successfully.';
        } catch (\Throwable $e) {
            \Log::error("Comment on Post Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function getAllPosts(Request $request)
    {
        try {
            $getPosts = Posts::with("comments", 'comments.userDetails', 'comments.subComments', 'comments.subComments.userDetails')->get(['postTitle', 'postContent']);
            if (Redis::get('postCount') != count($getPosts)) {
                Redis::del('postCount');
                Redis::del('getPosts');
            }
            $cachedPosts = Redis::get('getPosts');
            if (isset ($cachedPosts)) {
                $decodePosts = json_decode($cachedPosts, FALSE);
                $response['code'] = 200;
                $response['message'] = 'Fetched from redis';
                $response['data'] = $decodePosts;
            } else {
                Redis::set('postCount', count($getPosts));
                Redis::set('getPosts', $getPosts);
                $response['code'] = 200;
                $response['message'] = 'Fetched from database';
                $response['data'] = $getPosts;
            }
        } catch (\Throwable $e) {
            \Log::error("Get All Posts Request Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function addSubComment(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'commentId' => 'required',
                'userId' => 'required',
                'comment' => 'required',
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }

            $comment = new subComments;
            $comment->commentId = $request->commentId;
            $comment->userId = $request->userId;
            $comment->comment = $request->comment;
            $comment->created_at = Carbon::now()->toDateTimeString();
            $comment->updated_at = Carbon::now()->toDateTimeString();
            $comment->save();
            $response['Status'] = 'success';
            $response['Message'] = 'Comment posted successfully.';
        } catch (\Throwable $e) {
            \Log::error("Comment on Post Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function createPost(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'postTitle' => 'required',
                'postDesc' => 'required',
                'userId' => 'required',
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }

            $post = new Posts();
            $post->postTitle = $request->postTitle;
            $post->postContent = $request->postDesc;
            $post->userId = $request->userId;
            $post->created_at = Carbon::now()->toDateTimeString();
            $post->updated_at = Carbon::now()->toDateTimeString();
            $post->save();
            $response['Status'] = 'success';
            $response['Message'] = 'You created a Post successfully.';
        } catch (\Throwable $e) {
            \Log::error("Creates Post Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function likePost(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'postId' => 'required',
                'userId' => 'required',
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }

            $like = new Likes();
            $like->postId = $request->postId;
            $like->userId = $request->userId;
            $like->created_at = Carbon::now()->toDateTimeString();
            $like->updated_at = Carbon::now()->toDateTimeString();
            $like->save();
            $response['Status'] = 'success';
            $response['Message'] = 'You Liked a post successfully.';
        } catch (\Throwable $e) {
            \Log::error("Like Post Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function addFollowing(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'followingId' => 'required',
                'userId' => 'required',
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            if ($request->userId === $request->followingId) {
                return response()->json(['message' => "You are not allowed to follow yourself :("], 422);
            }
            $following = new Following();
            $following->userId = $request->userId;
            $following->followingId = $request->followingId;
            $following->created_at = Carbon::now()->toDateTimeString();
            $following->updated_at = Carbon::now()->toDateTimeString();
            $following->save();
            $response['Status'] = 'success';
            $response['Message'] = 'You are following a User.';
        } catch (\Throwable $e) {
            \Log::error("Following a user Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function dashboard(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'userId' => 'required',
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            $userId = array($request->userId);
            $followingids = [];
            $getfollowingids = Following::select('followingId')->where('userId', $request->userId)->get()->toArray();
            foreach ($getfollowingids as $following) {
                array_push($followingids, $following['followingId']);
            }
            $restrictIds = array_merge($userId, $followingids);
            $publicPosts = Posts::whereNotIn('userId', $restrictIds)->get();
            $followingPosts = Posts::whereIn('userId', $followingids)->get();
            $pcount = count($publicPosts);
            $fcount = count($followingPosts);
            $newpost = [];
            $postInd = 0;
            $followInd = 0;
            for ($k = 0; $k < $pcount; $k++) {
                if ($postInd === $pcount && $followInd === $fcount)
                    break;
                for ($i = 0; $i < 2; $i++) {
                    if ($postInd >= $pcount) {
                        break;
                    }
                    array_push($newpost, $publicPosts[$postInd]);
                    $postInd++;
                }
                for ($j = 0; $j < 2; $j++) {
                    if ($followInd >= $fcount) {
                        break;
                    }
                    array_push($newpost, $followingPosts[$followInd]);
                    $followInd++;
                }
            }
            $getFollowing = Following::with('users')->where('userId', $request->userId)->get();
            $followers = [];
            for ($i = 0; $i < count($getFollowing); $i++) {
                array_push($followers, $getFollowing[$i]['followingId']);
            }           
            $response['message'] = 'Success';
            $response['data'] = $newpost;
            $response['data']['followers'] = $followers;
        } catch (\Throwable $e) {
            \Log::error("Fetching Dashboard data Failed --->" . $e->getMessage());
        }
        return $response;
    }


}
