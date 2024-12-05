<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LocaltionController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use App\Http\Controllers\WishListController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication routes
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth',
], function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/facebook', [AuthController::class, 'redirectToFacebook']);
    Route::get('/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
        Route::post('password/reset', [AuthController::class, 'resetPassword']);

        Route::get('/wishlist', [WishListController::class, 'index']);
        Route::post('/wishlist', [WishListController::class, 'store']);
        Route::delete('/wishlist/{id}', [WishListController::class, 'destroy']);

        Route::apiResource('/addresses', App\Http\Controllers\AddressController::class);
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/add', [CartController::class, 'addItem']);
        Route::put('/cart/increase-quantity/{itemId}', [CartController::class, 'increaseItem']);
        Route::put('/cart/decrease-quantity/{itemId}', [CartController::class, 'decreaseItem']);
        Route::delete('/cart/remove/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('/cart/clear', [CartController::class, 'clearCart']);

        Route::get('/checkout/data', [CheckoutController::class, 'getCheckoutData']);
        Route::post('/checkout/discount', [CheckoutController::class, 'applyDiscount']);
        Route::post('/checkout/shipping', [CheckoutController::class, 'getShippingFeeForCart']);
        Route::post('/checkout', [CheckoutController::class, 'checkout']);
        Route::get('/vnpay/callback', [CheckoutController::class, 'vnpayCallback']);


        Route::get('/orders', [OrderController::class, 'userIndex']);
        Route::get('/orders/{id}', [OrderController::class, 'userShow']);
        Route::put('/orders/{id}/status', [OrderController::class, 'userUpdateStatus']);


    });
});

// Admin routes
Route::prefix('admin')
    ->middleware(['auth:api', AdminMiddleware::class])
    ->group(function () {
        Route::apiResource('/publishers', App\Http\Controllers\PublisherController::class);
        Route::apiResource('/authors', App\Http\Controllers\AuthorController::class);
        Route::apiResource('/translators', App\Http\Controllers\TranslatorController::class);
        Route::apiResource('/categories', App\Http\Controllers\CategoryController::class);
        Route::apiResource('/books', App\Http\Controllers\BookController::class);

        // Các route của admin
        Route::get('/orders', [OrderController::class, 'adminIndex']);
        Route::get('/orders/{id}', [OrderController::class, 'adminShow']);
        Route::put('/orders/{id}', [OrderController::class, 'adminUpdateStatus']);
        Route::delete('/orders/{id}', [OrderController::class, 'adminDestroy']);
        Route::get('/orders/statistics', [OrderController::class, 'statistics']);
    });


// Location-related routes
Route::get('/provinces', [LocaltionController::class, 'getProvince']);
Route::get('/districts', [LocaltionController::class, 'getDistrict']);
Route::get('/wards', [LocaltionController::class, 'getWard']);

// Search route
Route::get('books/search', [App\Http\Controllers\BookController::class, 'search']);
Route::apiResource('/publishers', App\Http\Controllers\PublisherController::class)->only(['index', 'show']);
Route::apiResource('/authors', App\Http\Controllers\AuthorController::class)->only(['index', 'show']);
Route::apiResource('/translators', App\Http\Controllers\TranslatorController::class)->only(['index', 'show']);
Route::apiResource('/categories', App\Http\Controllers\CategoryController::class)->only(['index', 'show']);
Route::apiResource('/books', App\Http\Controllers\BookController::class)->only(['index', 'show']);
Route::apiResource('/covertypes', App\Http\Controllers\CoverTypeController::class)->only(['index'])->only(['index', 'show']);
Route::apiResource('/languages', App\Http\Controllers\LanguageController::class)->only(['index'])->only(['index', 'show']);
Route::apiResource('/genres', App\Http\Controllers\GenreController::class)->only(['index'])->only(['index', 'show']);
Route::apiResource('/discounts', App\Http\Controllers\DiscountController::class)->only(['index', 'show']);

Route::apiResource('/covertypes', App\Http\Controllers\CoverTypeController::class)->only(['index']);
Route::apiResource('/languages', App\Http\Controllers\LanguageController::class)->only(['index']);
Route::apiResource('/genres', App\Http\Controllers\GenreController::class)->only(['index']);
