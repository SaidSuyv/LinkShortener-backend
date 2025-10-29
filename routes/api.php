<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PasswordResetController;

Route::post("login", [AuthController::class, "login"]);
Route::post("register", [UserController::class, "register"]);
Route::post('forgot-password', [PasswordResetController::class,'sendResetLink']);
Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware(["auth:sanctum"])->group(function(){
    Route::get("logout",[AuthController::class,"logout"]);

    Route::controller(LinkController::class)
    ->prefix("/link")
    ->group(function(){
        Route::get("/","index");
        Route::post("/","store");
        Route::get("/{code}","show");
        Route::put("/{link}","update");
        Route::delete("/{link}","destroy");
        Route::post("/restore/{link}","restore");
    });
});
