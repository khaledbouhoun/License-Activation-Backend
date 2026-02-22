<?php

use App\Http\Controllers\ActivationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\LicenseController;



// Activation routes accessible by client apps & desktop
Route::post('activate', [ActivationController::class, 'activate']);
Route::post('check', [ActivationController::class, 'check']);
Route::get('test', function () {
    return response()->json(['message' => 'Test passed']);
});

// Desktop-only CRUD routes protected by DESKTOP_APP_KEY
Route::middleware('desktop.auth')->group(function () {
    Route::apiResource('applications', ApplicationController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::apiResource('licenses', LicenseController::class);
    // test 
});



