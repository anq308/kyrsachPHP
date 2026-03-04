<?php

use App\Http\Controllers\Api\SpaApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('/bootstrap', [SpaApiController::class, 'bootstrap']);
    Route::get('/home', [SpaApiController::class, 'home']);
    Route::get('/catalog', [SpaApiController::class, 'catalog']);
    Route::get('/motorcycles/{id}', [SpaApiController::class, 'motorcycle']);

    Route::get('/cart', [SpaApiController::class, 'cartIndex']);
    Route::post('/cart/{id}', [SpaApiController::class, 'cartAdd']);
    Route::delete('/cart/{id}', [SpaApiController::class, 'cartRemove']);
    Route::post('/checkout', [SpaApiController::class, 'checkout']);

    Route::get('/compare', [SpaApiController::class, 'compareIndex']);
    Route::post('/compare/{id}', [SpaApiController::class, 'toggleCompare']);

    Route::post('/contact', [SpaApiController::class, 'contact']);

    Route::get('/me', [SpaApiController::class, 'me']);
    Route::post('/login', [SpaApiController::class, 'login']);
    Route::post('/register', [SpaApiController::class, 'register']);
    Route::post('/logout', [SpaApiController::class, 'logout']);

    Route::middleware('auth')->group(function () {
        Route::get('/favorites', [SpaApiController::class, 'favoritesIndex']);
        Route::post('/favorites/{id}', [SpaApiController::class, 'toggleFavorite']);
        Route::get('/profile', [SpaApiController::class, 'profile']);
    });

    Route::middleware(['auth', 'is_admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [SpaApiController::class, 'adminDashboard']);
        Route::post('/motorcycles', [SpaApiController::class, 'adminStoreMotorcycle']);
        Route::put('/motorcycles/{id}', [SpaApiController::class, 'adminUpdateMotorcycle']);
        Route::delete('/motorcycles/{id}', [SpaApiController::class, 'adminDeleteMotorcycle']);
        Route::patch('/orders/{id}/status', [SpaApiController::class, 'adminUpdateOrderStatus']);
    });
});

Route::view('/{any}', 'app')->where('any', '^(?!api).*$');

