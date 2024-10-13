<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth',
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('/profile', [AuthController::class, 'profile'])->middleware('auth:api');

});
Route::apiResource('/publishers', App\Http\Controllers\PublisherController::class);
Route::apiResource('/authors', App\Http\Controllers\AuthorController::class);
Route::apiResource('/translators', App\Http\Controllers\TranslatorController::class);
Route::apiResource('/co_publishers', App\Http\Controllers\CoPublisherController::class);
Route::apiResource('/categories', App\Http\Controllers\CategoryController::class);
Route::apiResource('/books', App\Http\Controllers\BookController::class);
Route::apiResource('addresses', App\Http\Controllers\AddressController::class);
