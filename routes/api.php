<?php

use App\Http\Controllers\Api\StocksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| Loaded with the "api" middleware group and the /api prefix
| (bootstrap/app.php). Token auth: users.api_token (config/auth.php "api").
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->get('/stocks', [StocksController::class, 'index']);
Route::middleware('auth:api')->get('/stocks/csv', [StocksController::class, 'csv']);
Route::middleware('auth:api')->get('/stocks/all', [StocksController::class, 'all']);
