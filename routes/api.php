<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SiteController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::apiResource('categories', CategoryController::class);
Route::apiResource('cars', CarController::class);
Route::apiResource('sliders', SliderController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('payment-methods', PaymentMethodController::class);
Route::apiResource('rentals', RentalController::class);
Route::apiResource('addons', AddonController::class);
Route::apiResource('reviews', ReviewController::class);
Route::apiResource('site-settings', SiteController::class);
