<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //Get All Notifications
    public function getNotifications(){
        $notifications = Notification::all();

        return success($notifications, null);
    }
}