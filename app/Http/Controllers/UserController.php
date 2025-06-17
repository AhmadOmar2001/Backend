<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\EditUserRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //Create User Function
    public function createUser(CreateUserRequest $createUserRequest)
    {
        $admin = Auth::guard('user')->user();
        if ($createUserRequest->password !== $createUserRequest->confirm_password) {
            return error('some thing went wrong', 'incorrect password confirmation', 422);
        }

        $user = User::create([
            'first_name' => $createUserRequest->first_name,
            'last_name' => $createUserRequest->last_name,
            'email' => $createUserRequest->email,
            'account_type' => $createUserRequest->account_type,
            'password' => Hash::make($createUserRequest->password),
        ]);

        Notification::create([
            'operation_type' => 'insert',
            'description' => $admin->first_name . ' ' . $admin->last_name . ' create user account for: ' . $user->first_name . ' ' . $user->last_name
        ]);
        return success(null, 'user created successfully', 201);
    }

    //Update User Function
    public function updateUser(User $user, EditUserRequest $editUserRequest)
    {
        $admin = Auth::guard('user')->user();
        if ($editUserRequest->password) {
            $editUserRequest->validate([
                'confirm_password' => 'required',
            ]);
            if ($editUserRequest->password !== $editUserRequest->confirm_password) {
                return error('some thing went wrong', 'incorrect password confirmation', 422);
            }
        }
        $editUserRequest->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'first_name' => $editUserRequest->first_name,
            'last_name' => $editUserRequest->last_name,
            'email' => $editUserRequest->email,
            'password' => $editUserRequest->password ? Hash::make($editUserRequest->password) : $user->password,
        ]);
        Notification::create([
            'operation_type' => 'update',
            'description' => $admin->first_name . ' ' . $admin->last_name . ' updated user account for: ' . $user->first_name . ' ' . $user->last_name
        ]);
        return success(null, 'user updated successfully');
    }

    //Delete User Function
    public function deleteUser(User $user)
    {
        $admin = Auth::guard('user')->user();
        Notification::create([
            'operation_type' => 'delete',
            'description' => $admin->first_name . ' ' . $admin->last_name . ' deleted user account: ' . $user->first_name . ' ' . $user->last_name
        ]);
        $user->delete();

        return success(null, 'user deleted successfully');
    }

    //Get Users Function
    public function getUsers(Request $request)
    {
        $users = User::where('account_type', $request->account_type)->get();

        return success($users, null);
    }

    //Get User Information Function
    public function getUserInformation(User $user)
    {
        return success($user, null);
    }
}
