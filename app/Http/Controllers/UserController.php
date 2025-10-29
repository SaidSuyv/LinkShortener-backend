<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponser;

    public function register(Request $request)
    {
        $rules = [
            "name" => "string|required",
            "lastname" => "string|required",
            "email" => "string|required|email|unique:users,email",
            "password" => "string|required|confirm"
        ];

        $messages = [
            "name.string" => "Name field should be a string",
            "name.required" => "Name field is required",
            "lastname.string" => "Lastname field should be a string",
            "lastname.required" => "Lastname field is required",
            "email.string" => "Email field should be a string",
            "email.required" => "Email field is required",
            "email.email" => "Email is not valid",
            "email.users" => "Email already exists",
            "password.string" => "Password field should be string",
            "password.required" => "Password field is required",
            "password.confirm" => "Password is not confirmed"
        ];

        $this->validate($request,$rules,$messages);

        $user = User::create([
            "name" => $request->name,
            "lastname" => $request->lastname,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        $token = $user->createToken("auth_token")->plainTextToken;

        return $this->successResponse([
            "token" => $token
        ]);
    }
}
