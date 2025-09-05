<?php

namespace App\Traits;

trait ApiResponser
{
    public function successResponse($data, int $code = 200)
    {
        return response()->json(
            [
                "success" => true,
                "data" => $data
            ],
            $code
        );
    }

    public function errorResponse(string $message, int $code = 500)
    {
        return response()->json(
            [
                "success" => false,
                "message" => $message
            ], 
            $code
        );
    }
}
