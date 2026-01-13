<?php

use App\Http\Controllers\Api\Auth\ApiLoginController;
use App\Http\Controllers\Api\Auth\ApiRegisteredUserController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [ApiRegisteredUserController::class, 'store'])
    ->middleware('guest:sanctum');

Route::post('/auth/login', [ApiLoginController::class, 'store']);

Route::middleware(['auth:sanctum'])->get('/user', UserController::class);

Route::apiResource('posts', PostController::class);

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('comments/{comment}', [CommentController::class, 'show']);
    Route::post('comments', [CommentController::class, 'store']);
    Route::match(['put', 'patch'], 'comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
});
