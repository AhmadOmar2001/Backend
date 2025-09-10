<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingRequest;
use App\Models\Hall;
use App\Models\HallRate;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class HallRateController extends Controller
{
    //Rating Hall Function
    public function Rating(Hall $hall, RatingRequest $ratingRequest)
    {
        $user = Auth::guard('user')->user();
        $hallRate = $hall->rates()->where('user_id', $user->id)->first();
        if ($hallRate) {
            $hallRate->update([
                'stars' => $ratingRequest->stars,
                'comment' => $ratingRequest->comment,
            ]);

            Notification::create([
                'operation_type' => 'update',
                'description' => $user->first_name . ' ' . $user->last_name . 'updated his rating for hall: ' . $hall->hall_name
            ]);
            return success(null, 'your rate updated successfully');
        }

        HallRate::create([
            'user_id' => $user->id,
            'hall_id' => $hall->id,
            'stars' => $ratingRequest->stars,
            'comment' => $ratingRequest->comment,
        ]);

        Notification::create([
            'operation_type' => 'insert',
            'description' => $user->first_name . ' ' . $user->last_name . 'rating hall: ' . $hall->hall_name
        ]);
        return success(null, 'your rate created successfully', 201);
    }
}