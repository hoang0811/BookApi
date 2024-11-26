<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LocaltionController;
use App\Http\Controllers\CheckoutController;
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

Route::get('/provinces', [LocaltionController::class, 'getProvince']);
Route::get('/districts', [LocaltionController::class, 'getDistrict']);
Route::get('/wards', [LocaltionController::class, 'getWard']);

Route::apiResource('/publishers', App\Http\Controllers\PublisherController::class);
Route::apiResource('/authors', App\Http\Controllers\AuthorController::class);
Route::apiResource('/translators', App\Http\Controllers\TranslatorController::class);
Route::apiResource('/categories', App\Http\Controllers\CategoryController::class);
Route::apiResource('/books', App\Http\Controllers\BookController::class);
Route::apiResource('/covertypes', App\Http\Controllers\CoverTypeController::class)->only(['index']);
Route::apiResource('/languages', App\Http\Controllers\LanguageController::class)->only(['index']);
Route::apiResource('/genres', App\Http\Controllers\GenreController::class)->only(['index']);
Route::apiResource('/discounts', App\Http\Controllers\DiscountController::class);

Route::get('/cart', [CartController::class, 'index'])->middleware('auth:api');
Route::post('/cart/add', [CartController::class, 'addItem'])->middleware('auth:api');
Route::put('/cart/increase-quantity/{itemId}', [CartController::class, 'increaseItem'])->middleware('auth:api');
Route::put('/cart/decrease-quantity/{itemId}', [CartController::class, 'decreaseItem'])->middleware('auth:api');
Route::delete('/cart/remove/{itemId}', [CartController::class, 'removeItem'])->middleware('auth:api');
Route::delete('/cart/clear', [CartController::class, 'clearCart'])->middleware('auth:api');

Route::get('/checkout/data', [CheckoutController::class, 'getCheckoutData'])->middleware('auth:api');
Route::post('/checkout/discount', [CheckoutController::class, 'applyDiscount'])->middleware('auth:api');
Route::post('/checkout/shipping', [CheckoutController::class, 'getShippingFeeForCart'])->middleware('auth:api');
Route::post('/checkout', [CheckoutController::class, 'checkout'])->middleware('auth:api');

