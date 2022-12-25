<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [App\Http\Controllers\Api\UserControllerAPI::class, 'login']);
Route::post('/register', [App\Http\Controllers\Api\UserControllerAPI::class, 'register']);

Route::prefix('food')->group(function () {
    Route::get('/all', [App\Http\Controllers\Api\FoodControllerAPI::class, 'allFoods']);
    Route::post('/create', [App\Http\Controllers\Api\FoodControllerAPI::class, 'createFood']);
});