<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FasilitasController;
use App\Http\Controllers\PeminjamanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Semua route API didefinisikan di sini.
|
*/

// route test koneksi api
Route::get('/test', fn() => response()->json(['message' => 'API Connected Successfully']));

// autentikasi
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Public Routes (tidak perlu login)
|--------------------------------------------------------------------------
*/

// rekomendasi fasilitas
Route::get('/fasilitas/rekomendasi', [FasilitasController::class, 'rekomendasi']);

// lihat daftar & detail fasilitas
Route::get('/fasilitas', [FasilitasController::class, 'index']);
Route::get('/fasilitas/{fasilitas}', [FasilitasController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (perlu login sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // data user login
    Route::get('/user', fn(Request $request) => $request->user());

    /*
    |--------------------------------------------------------------------------
    | fasilitas (admin/staff)
    |--------------------------------------------------------------------------
    */
    Route::post('/fasilitas', [FasilitasController::class, 'store']);
    Route::put('/fasilitas/{fasilitas}', [FasilitasController::class, 'update']);
    Route::delete('/fasilitas/{fasilitas}', [FasilitasController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | peminjaman (warga / rt / staff)
    |--------------------------------------------------------------------------
    */

    // endpoint custom (harus dideklarasi dulu biar tidak bentrok dengan apiResource)
    Route::post('/peminjaman/ajukan', [PeminjamanController::class, 'store']); // warga
    Route::patch('/peminjaman/setujui/{id}', [PeminjamanController::class, 'setujui']); // rt/rw
    Route::patch('/peminjaman/tolak/{id}', [PeminjamanController::class, 'tolak']); // rt/rw
    Route::patch('/peminjaman/serahkan/{id}', [PeminjamanController::class, 'serahkan']); // staff
    Route::patch('/peminjaman/kembalikan/{id}', [PeminjamanController::class, 'kembalikan']); // staff

    // resource default: index, show, update, destroy (tanpa store)
    Route::apiResource('peminjaman', PeminjamanController::class)->except(['store']);
});
