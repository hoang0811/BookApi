<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
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
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:api');

    Route::apiResource('/addresses', App\Http\Controllers\AddressController::class)->middleware('auth:api');
});

Route::get('/provinces', [App\Http\Controllers\LocationController::class, 'showAllProvinces']);
Route::get('/provinces/{provinceId}/districts', [App\Http\Controllers\LocationController::class, 'showDistrictsByProvince']);
Route::get('/districts/{districtId}/wards', [App\Http\Controllers\LocationController::class, 'showWardsByDistrict']);

Route::apiResource('/publishers', App\Http\Controllers\PublisherController::class);
Route::apiResource('/authors', App\Http\Controllers\AuthorController::class);
Route::apiResource('/translators', App\Http\Controllers\TranslatorController::class);
Route::apiResource('/categories', App\Http\Controllers\CategoryController::class);
Route::apiResource('/books', App\Http\Controllers\BookController::class);
Route::apiResource('/covertypes', App\Http\Controllers\CoverTypeController::class)->only(['index']);
Route::apiResource('/languages', App\Http\Controllers\LanguageController::class)->only(['index']);
Route::apiResource('/genres', App\Http\Controllers\GenreController::class)->only(['index']);
Route::apiResource('/discounts', App\Http\Controllers\DiscountController::class);

Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'addItem']);
Route::post('/cart/apply-coupon', [CartController::class, 'applyDiscount']);
Route::delete('/cart/remove-coupon', [CartController::class, 'removeCoupon']);
Route::put('/cart/increase-quantity/{rowId}', [CartController::class, 'increaseItem']);
Route::put('/cart/decrease-quantity/{rowId}', [CartController::class, 'decreaseItem']);
Route::delete('/cart/remove/{rowId}', [CartController::class, 'removeItem']);
Route::delete('/cart/clear', [CartController::class, 'clearCart']);