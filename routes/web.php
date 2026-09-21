<?php

use App\Http\Controllers\PublicSignoffController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['name' => 'TerminLock', 'status' => 'operational']);
});

Route::prefix('sign')->group(function () {
    Route::get('/{token}', [PublicSignoffController::class, 'show']);
    Route::post('/{token}/approve', [PublicSignoffController::class, 'approve']);
    Route::post('/{token}/revise', [PublicSignoffController::class, 'revise']);
});
