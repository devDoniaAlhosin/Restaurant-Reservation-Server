<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getUserNotifications(Request $request)
{
    $user = $request->user(); // Retrieve the authenticated user
    $notifications = $user->notifications; // Retrieve all notifications
    $unreadNotifications = $user->unreadNotifications; // Retrieve only unread notifications

    return response()->json([
        'all_notifications' => $notifications,
        'unread_notifications' => $unreadNotifications,
    ]);
}

}
