<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    //Get All Notifications
    public function getNotifications(){
        $notifications = Notification::all();

        return success($notifications, null);
    }
}