<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventDetailsRequest;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Models\EventOption;
use App\Models\Hall;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    //Create Event Function
    public function createEvent(EventRequest $eventRequest)
    {
        $user = Auth::guard('user')->user();

        if ($eventRequest->start_date > $eventRequest->end_date) {
            return error('some thing went wrong', 'error in your dates', 422);
        }

        $event = Event::create([
            'user_id' => $user->id,
            'event_name' => $eventRequest->event_name,
            'event_type' => $eventRequest->event_type,
            'start_date' => $eventRequest->start_date,
            'end_date' => $eventRequest->end_date,
        ]);

        Notification::create([
            'operation_type' => 'insert',
            'description' => $user->first_name . ' ' . $user->last_name . ' create an event: ' . $event->event_name
        ]);
        return success(null, 'your event created successfully', 201);
    }

    //Setup Event Details Function
    public function setupEventDetails(Event $event, EventRequest $eventRequest, EventDetailsRequest $eventDetailsRequest)
    {
        $user = Auth::guard('user')->user();
        $hall = Hall::find($eventDetailsRequest->hall);
        if ($eventDetailsRequest->seats_number > $hall->seats_number) {
            return error('some thing went wrong', 'no enough seats', 422);
        }

        if ($eventRequest->start_date > $eventRequest->end_date) {
            return error('some thing went wrong', 'error in your dates', 422);
        }

        if (
            Carbon::now()->toDateString() > Carbon::createFromFormat('Y-m-d', $eventRequest->start_date)->toDateString() ||
            Carbon::now()->toDateString() > Carbon::createFromFormat('Y-m-d', $eventRequest->end_date)->toDateString()
        ) {
            return error('something went wrong', 'error in your dates', 422);
        }

        foreach ($hall->events as $ev) {
            if ($ev->id != $event->id) {
                if ($ev->start_date == $eventRequest->start_date || $ev->end_date == $eventDetailsRequest->end_date || ($ev->start_date <= $eventRequest->start_date && $ev->end_date >= $eventRequest->end_date)) {
                    return error('something went wrong', 'this date already reserved', 422);
                }
            }
        }

        $event->update([
            'event_name' => $eventRequest->event_name,
            'event_type' => $eventRequest->event_type,
            'start_date' => $eventRequest->start_date,
            'end_date' => $eventRequest->end_date,
            'hall_id' => $eventDetailsRequest->hall,
            'seats_number' => $eventDetailsRequest->seats_number,
            'description' => $eventDetailsRequest->description,
        ]);

        $options_array = $eventDetailsRequest->input('options');

        if ($options_array) {

            foreach ($options_array as $option) {
                $event_option = EventOption::where('event_id', $event->id)->where('option_id', $option)->first();
                if (!$event_option) {
                    EventOption::create([
                        'event_id' => $event->id,
                        'option_id' => $option,
                    ]);
                }
            }
        } else {
            $options_array = [];
        }

        $options = EventOption::where('event_id', $event->id)->whereNotIn('option_id', $options_array)->get();
        foreach ($options as $option) {
            $option->delete();
        }

        Notification::create([
            'operation_type' => 'update',
            'description' => $user->first_name . ' ' . $user->last_name . 'updated his event: ' . $event->event_name
        ]);

        return success($options_array, 'event updated successfully');
    }

    //Delete Event Function
    public function deleteEvent(Event $event)
    {
        $user = Auth::guard('user')->user();
        Notification::create([
            'operation_type' => 'delete',
            'description' => $user->first_name . ' ' . $user->last_name . 'deleted his event ' . $event->event_name
        ]);

        $event->delete();

        return success(null, 'event deleted successfully');
    }

    //Get Events Function
    public function getEvents(Request $request)
    {
        $events = Event::with('hall', 'options')->where('event_type', $request->event_type)->whereNot('hall_id', null)->get();

        return success($events, null);
    }

    //Get My Events Function
    public function getMyEvents()
    {
        $user = Auth::guard('user')->user();
        $events = $user->events()->with('hall.options', 'options')->get();

        return success($events, null);
    }

    //Get Event Information Function
    public function getEventInformation(Event $event)
    {
        $event = $event->with('hall.options', 'options')->find($event->id);

        return success($event, null);
    }
}
