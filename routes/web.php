<?php

use App\Http\Controllers\HoldController;
use App\Http\Controllers\SlotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('api')->middleware(['api'])->group(function () {
    Route::post('/slots/available', [SlotController::class, 'slots']);

    Route::post('/slots/{slotId}/hold', [HoldController::class, 'hold']);
    Route::post('/holds/{hold}/confirm', [HoldController::class, 'confirm']);
    Route::post('/holds/{hold}/cancel',  [HoldController::class, 'cancel']);
});
