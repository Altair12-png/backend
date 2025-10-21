<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1️⃣ Validasi input dari Flutter
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|in:warga,staff,rtrw',
        ]);

        //  Cek apakah username terdaftar
        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'Username tidak ditemukan.'
            ], 404);
        }

        //  Verifikasi password (tanpa Auth::attempt)
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Password salah.'
            ], 401);
        }

        //  Verifikasi role
        if ($user->role !== $credentials['role']) {
            return response()->json([
                'message' => "Role akun tidak sesuai. Anda harus masuk sebagai {$user->role}.",
            ], 401);
        }

        //  (Opsional) Buat token login jika kamu sudah pakai Sanctum
        // $token = $user->createToken('auth_token')->plainTextToken;

        //  Respon sukses
        return response()->json([
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nama' => $user->nama,
                'role' => $user->role,
            ],
            // 'token' => $token, 
        ], 200);
    }
}
