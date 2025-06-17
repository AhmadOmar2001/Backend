<?php

namespace App\Http\Controllers;

use App\Http\Requests\CodeRequest;
use App\Http\Requests\EditPasswordRequest;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\ForgotPasswprdRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\VerificationMessage;
use App\Models\Notification;
use App\Models\User;
use App\Models\Verification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthenticationController extends Controller
{
    //Register Function
    public function register(RegisterRequest $registerRequest)
    {
        if ($registerRequest->password !== $registerRequest->confirm_password) {
            return error("some thing went wrong", 'incorrect confirmation password', 422);
        }

        $user = Verification::create([
            'first_name' => $registerRequest->first_name,
            'last_name' => $registerRequest->last_name,
            'email' => $registerRequest->email,
            'password' => Hash::make($registerRequest->password),
            'account_type' => $registerRequest->account_type,
            'code' => rand(111111, 999999),
            'expiry_date' => Carbon::now()->addMinutes(15),
        ]);

        try {
            Mail::to($registerRequest->email)->send(new VerificationMessage($user->code));
        } catch (Exception $e) {
            $user->delete();
            return error('some thing went wrong', 'cannot send verification code, try arain later....', 422);
        }

        $token = $user->createToken('user')->plainTextToken;

        return success($token, "We sent verificaion code to" . $user->email, 201);
    }

    //Resend Verification Code Function
    public function resendCode()
    {
        $user = Auth::guard('password')->user();

        $user->update([
            'code' => rand(111111, 999999),
            'expiry_date' => Carbon::now()->addMinutes(15),
        ]);

        try {
            Mail::to($user->email)->send(new VerificationMessage($user->code));
        } catch (Exception $e) {
            return error('some thing went wrong', 'cannot send verification code, try arain later....', 422);
        }

        return success(null, 'resent code successfully');
    }

    //Check Register Verification Code Function
    public function checkRegister(CodeRequest $codeRequest)
    {
        $verify = Auth::guard('password')->user();

        if ($verify->code == $codeRequest->code && Carbon::now() < $verify->expiry_date) {
            $user = User::create([
                'first_name' => $verify->first_name,
                'last_name' => $verify->last_name,
                'email' => $verify->email,
                'password' => $verify->password,
                'account_type' => $verify->account_type,
            ]);
            $verify->delete();
            $token = $user->createToken('user')->plainTextToken;

            Notification::create([
                'operation_type' => 'insert',
                'description' => $user->first_name . ' ' . $user->last_name . ' create an account in your system'
            ]);

            return success($token, "Your account created successfully", 201);
        }

        return error('some thing went wrong', 'incorrect code', 422);
    }

    //Login Function
    public function login(LoginRequest $loginRequest)
    {
        $user = User::where('email', $loginRequest->email)->first();
        if ($user && Hash::check($loginRequest->password, $user->password)) {
            $token = $user->createToken("user")->plainTextToken;

            return success($token, 'login successfully');
        }

        return error("some thing went wrong", 'incorrect email or password', 422);
    }

    //Profile Information Function
    public function profile()
    {
        $user = Auth::guard('user')->user();

        return success($user, null);
    }

    //Forgot Password Function
    public function ForgotPassword(ForgotPasswprdRequest $forgotPasswprdRequest)
    {
        $verify = Verification::create([
            'email' => $forgotPasswprdRequest->email,
            'code' => rand(111111, 999999),
            'expiry_date' => Carbon::now()->addMinutes(15),
        ]);

        try {
            Mail::to($verify->email)->send(new VerificationMessage($verify->code));
        } catch (Exception $e) {
            return error('some thing went wrong', 'cannot send verification code, try arain later....', 422);
        }

        $token = $verify->createToken('password')->plainTextToken;

        return success($token, 'We sent verification code to your email address', 201);
    }

    //Check Forgot Password Verification Code Function
    public function checkForgotPasswordVerificationCode(CodeRequest $codeRequest)
    {
        $verify = Auth::guard('password')->user();

        if ($verify->code == $codeRequest->code && Carbon::now() < $verify->expiry_date) {
            $user = User::where('email', $verify->email)->first();
            $token = $user->createToken('reset-password')->plainTextToken;
            $verify->delete();

            return success($token, null);
        }

        return error('some thing went wrong', 'incorrect code', 422);
    }

    //Reset Password Function
    public function resetPassword(ResetPasswordRequest $resetPasswordRequest)
    {
        $user = Auth::guard('reset-password')->user();
        if ($resetPasswordRequest->new_password !== $resetPasswordRequest->confirm_new_password) {
            return error('some thing went wrong', 'incorrect password confirmation', 422);
        }

        $user->update([
            'password' => Hash::make($resetPasswordRequest->new_password)
        ]);

        Notification::create([
            'operation_type' => 'update',
            'description' => $user->first_name . ' ' . $user->last_name . ' reset his password'
        ]);

        $user->tokens()->delete();


        return success(null, 'password reset successfully');
    }

    //Logout Function
    public function logout()
    {
        $user = Auth::guard('user')->user();

        $user->tokens()->delete();

        return success(null, 'logout successfully');
    }

    //Edit Profile Function
    public function editProfile(EditProfileRequest $editProfileRequest)
    {
        $user = Auth::guard('user')->user();

        $user->update([
            'first_name' => $editProfileRequest->first_name,
            'last_name' => $editProfileRequest->last_name,
        ]);

        Notification::create([
            'operation_type' => 'update',
            'description' => $user->first_name . ' ' . $user->last_name . ' edited his profile information'
        ]);

        return success(null, 'your profile updated successfully');
    }

    //Edit Password Function
    public function editPassword(EditPasswordRequest $editPasswordRequest)
    {
        $user = Auth::guard('user')->user();

        if (!Hash::check($editPasswordRequest->password, $user->password)) {
            return error('some thing went wrong', 'incorrect password', 422);
        }
        if ($editPasswordRequest->new_password !== $editPasswordRequest->confirm_new_password) {
            return error('some thing went wrong', 'incorrect new password confirmation', 422);
        }

        $user->update([
            'password' => Hash::make($editPasswordRequest->new_password)
        ]);

        Notification::create([
            'operation_type' => 'update',
            'description' => $user->first_name . ' ' . $user->last_name . ' edited his password'
        ]);

        return success(null, 'your password updated successfully');
    }
}