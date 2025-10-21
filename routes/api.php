<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Route default untuk test koneksi
Route::get('/test', function () {
    return response()->json(['message' => 'API Connected Successfully']);
});

// Route login (dari AuthController)
Route::post('/login', [AuthController::class, 'login']);
