<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FasilitasController; // Import Controller Fasilitas

// Route default untuk test koneksi
Route::get('/test', function () {
    return response()->json(['message' => 'API Connected Successfully']);
});

// Route login (dari AuthController)
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Public Routes (Dapat Diakses Warga untuk Melihat List Fasilitas)
|--------------------------------------------------------------------------
*/

// A. List Semua Fasilitas (Wajib untuk List Inventaris)
Route::get('/fasilitas', [FasilitasController::class, 'index']);

// B. List Rekomendasi (Opsional, untuk Dashboard yang lebih efisien)
Route::get('/fasilitas/rekomendasi', [FasilitasController::class, 'rekomendasi']);


/*
|--------------------------------------------------------------------------
| Authenticated Routes (Membutuhkan Token Akses)
|--------------------------------------------------------------------------
| Digunakan untuk fungsionalitas yang memerlukan user login (Staff/Warga/RT/RW).
*/

// Middleware 'auth:sanctum' akan memvalidasi token dari Flutter
Route::middleware('auth:sanctum')->group(function () {
    
    // Staff: Menambah Fasilitas Baru (Fungsionalitas CREATE)
    // Di sisi Controller/Middleware, harus dipastikan hanya user ber-role "Staff" yang bisa mengakses ini.
    Route::post('/fasilitas', [FasilitasController::class, 'store']);
    
    // [Rekomendasi Tambahan] Endpoint untuk Update dan Delete oleh Staff/Admin
    // Route::put('/fasilitas/{id}', [FasilitasController::class, 'update']);
    // Route::delete('/fasilitas/{id}', [FasilitasController::class, 'destroy']);
    
    // Endpoint user profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
