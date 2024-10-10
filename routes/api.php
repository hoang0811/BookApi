<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('/publishers', App\Http\Controllers\PublisherController::class);
Route::apiResource('/authors', App\Http\Controllers\AuthorController::class);
Route::apiResource('/translators', App\Http\Controllers\TranslatorController::class);
Route::apiResource('/co_publishers', App\Http\Controllers\CoPublisherController::class);
Route::apiResource('/categories', App\Http\Controllers\CategoryController::class);
Route::apiResource('/books', App\Http\Controllers\BookController::class);


