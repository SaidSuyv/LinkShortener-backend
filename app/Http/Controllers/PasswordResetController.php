<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    use ApiResponser;

    public function sendResetLink(Request $request)
    {
        $rules = ['email' => 'required|email'];
        $messages = [
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El valor enviado no es un correo electrónico'
        ];
        $this->validate($request, $rules, $messages);

        $status = Password::sendResetLink($request->only('email'));

        if($status === Password::RESET_LINK_SENT)
            return $this->successResponse('Correo de recuperación enviado');
        else
            return $this->errorResponse('No se pudo enviar el correo', 400);
    }

    public function resetPassword(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed'
        ];
        $messages = [
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'El correo electrónico debe tener el formato correcto de correo',
            'token.required' => 'El token es un campo obligatorio',
            'password.required' => 'La contraseña es un campo obligatorio',
            'password.min' => 'La contraseña debe tener como mínimo 8 carácteres',
            'password.confirmed' => 'La contraseña debe ser confirmada'
        ];
        $this->validate($request, $rules, $messages);

        $status = Password::reset(
            $request->only('email','password', 'password_confirmation', 'token'),
            function(User $user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if($status === Password::PASSWORD_RESET)
            return $this->successResponse('La contraseña ha sido restablecida');
        else
            return $this->errorResponse('Token inválido o expirado', 400);
    }
}
