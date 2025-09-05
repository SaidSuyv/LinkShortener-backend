<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    protected function validate(Request $request, array $rules, array $messages = []): void
    {
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails())
        {
            throw new ValidationException($validator);
        }
    }
}
