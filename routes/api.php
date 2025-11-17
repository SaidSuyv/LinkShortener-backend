<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PasswordResetController;

Route::post("login", [AuthController::class, "login"]);
Route::get("logout",[AuthController::class,"logout"]);
Route::post("register", [UserController::class, "register"]);
Route::post('forgot-password', [PasswordResetController::class,'sendResetLink']);
Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware(["auth:sanctum"])->group(function(){
    Route::controller(UserController::class)
    ->prefix('/user')
    ->group(function(){
        Route::get('/','getBasicData');
    });

    Route::controller(LinkController::class)
    ->prefix("/link")
    ->group(function(){
        // CRUD
        Route::get("/","index");
        Route::post("/","store");
        Route::get("/{code}","show");
        Route::put("/{link}","update");
        Route::delete("/{id}","destroy");
        Route::post("/restore/{link}","restore");

        // Bulk Routes
        Route::get('/bulk/status/{id}','batchStatus');
        Route::post('/bulk/delete','deleteBulk');
        Route::post('/bulk/restore','restoreBulk');
        Route::post('/bulk/hard-delete','hardDeleteBulk');

        // Premium functions
        Route::post('/keep/{link}','keepAlive');
    });
});
