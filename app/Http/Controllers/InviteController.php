<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteUserRequest;
use App\Models\Event;
use App\Models\InvitedUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InviteController extends Controller
{
    //Invite Users To The Event Function
    public function inviteUsers(Event $event, InviteUserRequest $inviteUserRequest)
    {
        $check_seats = InvitedUser::where('event_id', $event->id)->get();
        $check_seats2 = InvitedUser::whereIn('user_id', $inviteUserRequest->users)->get();
        $count = count($check_seats) + (count($inviteUserRequest->users) - count($check_seats2));

        if ($count > $event->seats_number) {
            return error("some thing went wrong", 'not enought seats in this hall', 422);
        }
        foreach ($inviteUserRequest->users as $user) {
            $check_user = InvitedUser::where('event_id', $event->id)->where('user_id', $user)->first();
            if ($check_user) {
                continue;
            } else {
                InvitedUser::create([
                    'event_id' => $event->id,
                    'user_id' => $user,
                ]);
            }
        }

        return success(null, 'users invited to this event successfully', 201);
    }

    //Remove User From Invitations List Function
    public function removeUser(InvitedUser $invitedUser)
    {
        if (Carbon::now() > $invitedUser->event->start_date && $invitedUser->is_accepted) {
            return error('some thing went wrong', 'you cant remove this user now', 422);
        }
        $invitedUser->delete();

        return success(null, 'this user removed successfully');
    }

    //Accept Invitation Request Function
    public function acceptRequest(Event $event)
    {
        $user = Auth::guard('user')->user();
        $invitedUser = InvitedUser::where('event_id', $event->id)->where('user_id', $user->id)->first();
        if ($invitedUser->event->start_date < Carbon::now()) {
            return error('some thing went wrong', 'you cant accept now', 422);
        }

        $invitedUser->update([
            'is_accepted' => 1,
        ]);

        return success(null, 'accepted successfully');
    }

    //Reject Invitation Request Function
    public function rejectRequest(Event $event)
    {
        $user = Auth::guard('user')->user();
        $invitedUser = InvitedUser::where('event_id', $event->id)->where('user_id', $user->id)->first();
        if ($invitedUser->event->start_date < Carbon::now()) {
            return error('some thing went wrong', 'you cant reject now', 422);
        }

        $invitedUser->delete();

        return success(null, 'rejected successfully');
    }

    //Get Invited Users Function
    public function getInvitedUsers(Event $event)
    {
        return success($event->invitations()->with('user')->get(), null);
    }

    //Get Events That User Invited To It Function
    public function getInvitedEvents()
    {
        $user = Auth::guard('user')->user();
        $events = $user->invitations()->with('invitedUsers', 'hall')->where('is_accepted', 0)->get();

        return success($events, null);
    }

    //Get Events That User Participant To It Function
    public function getParticipantEvents()
    {
        $user = Auth::guard('user')->user();
        $events = $user->invitations()->with('invitedUsers', 'hall')->where('is_accepted', 1)->get();

        return success($events, null);
    }
}