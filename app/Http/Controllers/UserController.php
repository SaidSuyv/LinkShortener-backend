<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthResource;
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
            "name" => "required",
            "lastname" => "required",
            "email" => "required|email|unique:users,email",
            "password" => "required|confirmed"
        ];

        $messages = [
            "name.required" => "Datos personales incompletos",
            "lastname.required" => "Datos personales incompletos",
            "email.required" => "El correo electrónico es un campo obligatorio",
            "email.email" => "El correo no es válido",
            "email.users" => "El correo ingresado ya existe",
            "password.required" => "La contraseña es obligatoria",
            "password.confirmed" => "Campos requeridos incompletos"
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
            "user" => new AuthResource($user),
            "token" => $token
        ]);
    }
}
