<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\HallRateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthenticationController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/forgot-password', 'ForgotPassword');
    Route::post('/check-forgot-password-code', 'checkForgotPasswordVerificationCode');
    Route::post('/reset-password', 'resetPassword')->middleware('reset-password');

    Route::middleware('password')->group(function () {
        Route::post('/resend-code', 'resendCode');
        Route::post('/check-register', 'checkRegister');
    });
    Route::middleware('authentication')->group(function () {
        Route::prefix('profile')->middleware('authentication')->group(function () {
            Route::get('/', 'profile');
            Route::post('/', 'editProfile');
        });
        Route::post('/edit-password', 'editPassword');
        Route::post('/logout', 'logout');
    });
});

Route::middleware('authentication')->group(function () {
    Route::controller(HallController::class)->prefix('halls')->group(function () {
        Route::get('/', 'getHalls');
        Route::get('/{hall}', 'getHallInformation');
        Route::get('/reservations/{hall}', 'getReservations');
        Route::middleware('hall')->group(function () {
            Route::post('/', 'createHall');
            Route::post('/{hall}', 'editHall');
            Route::delete('/{hall}', 'deleteHall');
            Route::delete('image/{hallImage}', 'deleteImage');
            Route::delete('/{hall}/{event}', 'removeReservation');
        });
    });
    Route::controller(OptionController::class)->prefix('options')->group(function () {
        Route::get('/{hall}', 'getHallOptions');
        Route::get('/option/{option}', 'getOptionInformation');
        Route::middleware('option')->group(function () {
            Route::post('/{hall}', 'createOption');
            Route::post('/option/{option}', 'editOption');
            Route::delete('/{option}', 'deleteOption');
        });
    });
    Route::controller(UserController::class)->middleware('user')->prefix('users')->group(function () {
        Route::get('/', 'getUsers');
        Route::get('/{user}', 'getUserInformation');
        Route::post('/', 'createUser');
        Route::post('/{user}', 'updateUser');
        Route::delete('/{user}', 'deleteUser');
    });
    Route::controller(HallRateController::class)->middleware('hall-rate')->prefix('halls-rates')->group(function () {
        Route::post('/{hall}', 'Rating');
    });
    Route::controller(EventController::class)->prefix('events')->group(function () {
        Route::get('/', 'getEvents');
        Route::get('/{event}', 'getEventInformation');
        Route::middleware('event')->group(function () {
            Route::post('/', 'createEvent');
            Route::post('/{event}', 'setupEventDetails');
            Route::delete('/{event}', 'deleteEvent');
        });
    });
    Route::controller(NotificationController::class)->middleware('notification')->prefix('notifications')->group(function (){
        Route::get('/', 'getNotifications');
    });
    Route::get('/my-events', [EventController::class, 'getMyEvents']);
});