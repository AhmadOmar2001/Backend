<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    //Send Friend Request Function
    public function sendRequest(User $user)
    {
        $sender = Auth::guard('user')->user();

        $friend = Friend::where(function ($query) use ($user, $sender) {
            $query->where('user1_id', $user->id)->where('user2_id', $sender->id);
        })->orWhere(function ($query) use ($user, $sender) {
            $query->where('user1_id', $sender->id)->where('user2_id', $user->id);
        })->first();

        if ($friend) {
            return error('some thing went wrong', 'this friend already sent to him friend request', 422);
        }

        Friend::create([
            'user1_id' => $sender->id,
            'user2_id' => $user->id,
        ]);

        return success(null, 'friend request sent to this user successfully', 201);
    }

    //Get Friends Function
    public function getFriends()
    {
        $user = Auth::guard('user')->user();
        $ids = [];

        $friends = Friend::where('user1_id', $user->id)->orWhere('user2_id', $user->id)->get();
        foreach ($friends as $friend) {
            if ($friend->user1_id == $user->id && $friend->status) {
                $ids[] = $friend->user2_id;
            } else if ($friend->user2_id == $user->id && $friend->status) {
                $ids[] = $friend->user1_id;
            }
        }

        $friends = User::whereIn('id', $ids)->get();

        return success($friends, null);
    }

    //Get Sent Friend Requests Function
    public function getSentFriendRequests()
    {
        $user = Auth::guard('user')->user();
        $ids = [];

        $friends = Friend::where('user1_id', $user->id)->get();
        foreach ($friends as $friend) {
            if ($friend->user1_id == $user->id && !$friend->status) {
                $ids[] = $friend->user2_id;
            }
        }

        $friends = User::whereIn('id', $ids)->get();

        return success($friends, null);
    }

    //Get Friend Requests Function
    public function getFriendRequests()
    {
        $user = Auth::guard('user')->user();
        $ids = [];

        $friends = Friend::where('user1_id', $user->id)->orWhere('user2_id', $user->id)->get();
        foreach ($friends as $friend) {
            if ($friend->user2_id == $user->id && !$friend->status) {
                $ids[] = $friend->user1_id;
            }
        }

        $friends = User::whereIn('id', $ids)->get();

        return success($friends, null);
    }

    //Accept Friend Request Function
    public function acceptFriendRequest(User $user)
    {
        $user2 = Auth::guard('user')->user();
        $friend = Friend::where('user1_id', $user->id)->where('user2_id', $user2->id)->first();
        $friend->update([
            'status' => 1,
        ]);

        return success(null, 'accepted successfully');
    }

    //Denie Friend Request Function
    public function denieFriendRequest(User $user)
    {
        $user2 = Auth::guard('user')->user();
        $friend = Friend::where('user1_id', $user->id)->where('user2_id', $user2->id)->first();
        $friend->delete();

        return success(null, 'denied successfully');
    }
}