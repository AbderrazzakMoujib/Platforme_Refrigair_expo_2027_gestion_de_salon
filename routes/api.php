<?php

use App\Http\Controllers\Api\V1\BadgeLookupController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/badges/lookup', BadgeLookupController::class)
        ->middleware(['throttle:badge-api', 'auth.badge-api'])
        ->name('badges.lookup');
});
