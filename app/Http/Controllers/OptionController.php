<?php

namespace App\Http\Controllers;

use App\Http\Requests\OptionRequest;
use App\Models\Hall;
use App\Models\Notification;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionController extends Controller
{
    //Create Option Function
    public function createOption(Hall $hall, OptionRequest $optionRequest)
    {
        $user = Auth::guard('user')->user();
        $option = Option::create([
            'hall_id' => $hall->id,
            'option' => $optionRequest->option
        ]);

        Notification::create([
            'operation_type' => 'insert',
            'description' => $user->first_name . ' ' . $user->last_name . ' create an option: ' . $option->option . ' for hall ' . $hall->hall_name
        ]);
        return success(null, 'option created successfully', 201);
    }

    //Edit Option Function
    public function editOption(Option $option, OptionRequest $optionRequest)
    {
        $user = Auth::guard('user')->user();
        $option->update([
            'option' => $optionRequest->option
        ]);

        Notification::create([
            'operation_type' => 'update',
            'description' => $user->first_name + ' ' + $user->last_name . 'edited option: ' . $option->option
        ]);

        return success(null, 'option updated successfully');
    }

    //Delete Option Function
    public function deleteOption(Option $option)
    {
        $user = Auth::guard('user')->user();
        Notification::create([
            'operation_type' => 'delete',
            'description' => $user->first_name + ' ' + $user->last_name . 'delete option: ' . $option->option
        ]);

        $option->delete();

        return success(null, 'option deleted successfully');
    }

    //Get Hall Options Function
    public function getHallOptions(Hall $hall)
    {
        return success($hall->options, null);
    }

    //Get Option Information Function
    public function getOptionInformation(Option $option)
    {
        return success($option, null);
    }
}
