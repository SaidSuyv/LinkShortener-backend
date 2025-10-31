<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\AuthResource;

class AuthController extends Controller
{
    use ApiResponser;

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        $messages = [
            'email.required' => 'The email field is required',
            'email.email' => 'The email is not valid',
            'password.required' => 'The password field is required'
        ];

        $this->validate($request, $rules, $messages);

        $user = User::where("email",$request->email)->first();

        if(!$user)
            return $this->errorResponse("User not found", 404);

        if(!Hash::check($request->password, $user->password) )
            return $this->errorResponse("Incorrect credentials", 401);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            "user" => new AuthResource($user),
            "token" => $token
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return $this->successResponse("Session closed");
    }
}
