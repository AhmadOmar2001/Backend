<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupportRequest;
use App\Models\Support;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    //Request Support Function
    public function requestSupport(SupportRequest $supportRequest)
    {
        $user = Auth::guard('user')->user();
        Support::create([
            'user_id' => $user->id,
            'request' => $supportRequest->support_request
        ]);

        return success(null, 'your request sent successfully', 201);
    }

    //Get Support Requests Function
    public function getSupportRequests()
    {
        $requests = Support::orderBy("id", "desc")->with('user')->get();

        return success($requests, null);
    }
}