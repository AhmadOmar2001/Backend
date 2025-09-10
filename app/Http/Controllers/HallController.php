<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHallRequest;
use App\Models\Event;
use App\Models\FavoriteHall;
use App\Models\Hall;
use App\Models\HallImage;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class HallController extends Controller
{
    //Get Halls Function
    public function getHalls(Request $request)
    {
        $user = Auth::guard('user')->user();
        $total_rate = 0;
        $halls_merge = [];
        if ($user->account_type == 'admin' || $user->account_type == 'regular_user') {
            $halls = Hall::with('images', 'options')->where('hall_name', "LIKE", '%' . $request->search . '%')->orWhere('location', "LIKE", '%' . $request->search . '%')->get();
            foreach ($halls as $hall) {
                foreach ($hall->rates as $rate) {
                    $total_rate += $rate->stars;
                }
                if (count($hall->rates) != 0) {
                    $total_rate /= count($hall->rates);
                }
                $rate = [
                    'total_rate' => $total_rate,
                ];
                $halls_merge[] = array_merge($hall->toArray(), $rate);
                $total_rate = 0;
            }
            return success($halls_merge, null);
        } else {
            $halls = $user->halls()->with('images', 'options')->where(function ($query) use ($request) {
                $query->where('hall_name', "LIKE", '%' . $request->search . '%')->orWhere('location', "LIKE", '%' . $request->search . '%');
            })->get();
            foreach ($halls as $hall) {
                foreach ($hall->rates as $rate) {
                    $total_rate += $rate->stars;
                }
                if (count($hall->rates) != 0) {
                    $total_rate /= count($hall->rates);
                }
                $rate = [
                    'total_rate' => $total_rate,
                ];
                $halls_merge[] = array_merge($hall->toArray(), $rate);
                $total_rate = 0;
            }
            return success($halls_merge, null);
        }
    }

    //Get Halls Ordered From High To Low Rates Function
    public function orderingHalls()
    {
        $user = Auth::guard('user')->user();
        $total_rate = 0;
        $halls = Hall::with('images', 'options')->get()->sortByDesc(function ($hall) {
            return $hall->average_rating;
        });
        foreach ($halls as $hall) {
            foreach ($hall->rates as $rate) {
                $total_rate += $rate->stars;
            }
            if (count($hall->rates) != 0) {
                $total_rate /= count($hall->rates);
            }
            $rate = [
                'total_rate' => $total_rate,
            ];
            $halls_merge[] = array_merge($hall->toArray(), $rate);
            $total_rate = 0;
        }
        return success($halls_merge, null);
    }

    //Get Hall Information Function
    public function getHallInformation(Hall $hall)
    {
        $total_rate = 0;
        $hall = $hall->with(['images', 'options', 'rates.user'])->with('events', function ($query) {
            $query->where('end_date', '>', Carbon::now()->toDateString());
        })->find($hall->id);
        foreach ($hall->rates as $rate) {
            $total_rate += $rate->stars;
        }
        if ($total_rate != 0) {
            $total_rate /= count($hall->rates);
        }
        $rate = [
            'total_rate' => $total_rate,
        ];
        $hall_merge[] = array_merge($hall->toArray(), $rate);
        return success($hall_merge[0], null);
    }

    //Create Hall Function
    public function createHall(CreateHallRequest $createHallRequest)
    {
        $user = Auth::guard('user')->user();

        $hall = Hall::create([
            'user_id' => $user->id,
            'hall_name' => $createHallRequest->hall_name,
            'seats_number' => $createHallRequest->seats_number,
            'location' => $createHallRequest->location,
            'description' => $createHallRequest->description,
        ]);

        if ($createHallRequest->file('images')) {
            $images = $createHallRequest->file('images');
            foreach ($images as $image) {
                $path = $image->storePublicly('HallsImages', 'public');

                HallImage::create([
                    'hall_id' => $hall->id,
                    'image' => 'storage/' . $path
                ]);
            }
        }

        Notification::create([
            'operation_type' => 'insert',
            'description' => $user->first_name . ' ' . $user->last_name . 'create a hall: ' . $hall->hall_name
        ]);

        return success(null, 'your hall created successfully', 201);
    }

    //Edit Hall Function
    public function editHall(Hall $hall, CreateHallRequest $createHallRequest)
    {
        $user = Auth::guard('user')->user();
        $hall->update([
            'hall_name' => $createHallRequest->hall_name,
            'seats_number' => $createHallRequest->seats_number,
            'location' => $createHallRequest->location,
            'description' => $createHallRequest->description
        ]);

        if ($createHallRequest->file('images')) {
            $images = $createHallRequest->file('images');
            foreach ($images as $image) {
                $path = $image->storePublicly('HallsImages', 'public');

                HallImage::create([
                    'hall_id' => $hall->id,
                    'image' => 'storage/' . $path
                ]);
            }
        }

        Notification::create([
            'operation_type' => 'update',
            'description' => $user->first_name . ' ' . $user->last_name . ' edited his hall ' . $hall->hall_name
        ]);

        return success(null, 'your hall updated successfully');
    }

    //Delete Hall Image Function
    public function deleteImage(HallImage $hallImage)
    {
        if (File::exists($hallImage->image)) {
            File::delete($hallImage->image);
        }

        $hallImage->delete();

        return success(null, 'image deleted successfully');
    }

    //Delete Hall Function
    public function deleteHall(Hall $hall)
    {
        $user = Auth::guard('user')->user();
        foreach ($hall->images as $image) {
            if (File::exists($image->image)) {
                File::delete($image->image);
            }

            $image->delete();
        }
        Notification::create([
            'operation_type' => 'delete',
            'description' => $user->first_name . ' ' . $user->last_name . 'deleted his hall ' . $hall->hall_name
        ]);
        $hall->delete();

        return success(null, 'your hall deleted successfully');
    }

    //Remove Hall Reservation Function
    public function removeReservation(Hall $hall, Event $event)
    {
        $user = Auth::guard('user')->user();
        foreach ($event->eventOptions as $option) {
            $option->delete();
        }

        Notification::create([
            'operation_type' => 'delete',
            'description' => $user->first_name . ' ' . $user->last_name . 'removed a reservation: ' . $event->event_name . ' from his hall ' . $hall->hall_name
        ]);

        $event->update([
            'hall_id' => null,
        ]);

        return success(null, 'this reservation removed successfully');
    }

    //Get Hall Reservations Function
    public function getReservations(Hall $hall)
    {
        $reservations = $hall->events()->orderby('start_date', 'desc')->get();
        return success($reservations, null);
    }

    //Add/Remove Hall To Favorite Function
    public function addRemoveFromFavorite(Hall $hall)
    {
        $user = Auth::guard('user')->user();
        $favorite_halls = $user->favoriteHalls;

        foreach ($favorite_halls as $favorite_hall) {
            if ($favorite_hall->id == $hall->id) {
                $favorite_hall = FavoriteHall::where('user_id', $user->id)->where('hall_id', $hall->id)->first();
                $favorite_hall->delete();
                return success(null, 'this hall removed from favorite');
            }
        }
        FavoriteHall::create([
            'user_id' => $user->id,
            'hall_id' => $hall->id
        ]);

        return success(null, 'this hall added to favorite', 201);
    }

    //Get Favorite Halls Function
    public function getFavoriteHalls()
    {
        $user = Auth::guard('user')->user();
        $favorite_halls = $user->favoriteHalls;
        $total_rate = 0;
        $halls_merge = [];

        foreach ($favorite_halls as $hall) {
            foreach ($hall->rates as $rate) {
                $total_rate += $rate->stars;
            }
            if (count($hall->rates) != 0) {
                $total_rate /= count($hall->rates);
            }
            $rate = [
                'total_rate' => $total_rate,
            ];
            $halls_merge[] = array_merge($hall->toArray(), $rate);
            $total_rate = 0;
        }

        return success($halls_merge, null);
    }
}