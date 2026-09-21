<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['name' => 'TerminLock', 'status' => 'operational']);
});
