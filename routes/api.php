<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

// ESP32 endpoints (no auth required for IoT device)
Route::post('/sensor-data', [ApiController::class, 'storeSensorData']);
Route::get('/device-status', [ApiController::class, 'getDeviceStatus']);

// Dashboard endpoints
Route::get('/latest', [ApiController::class, 'getLatest']);
Route::get('/history', [ApiController::class, 'getHistory']);
Route::post('/control', [ApiController::class, 'sendControl']);
Route::post('/alerts/read', [ApiController::class, 'markAlertsRead']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
