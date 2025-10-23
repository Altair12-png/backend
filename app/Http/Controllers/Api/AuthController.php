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
        // validasi input
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|in:warga,staff,rtrw',
        ]);

        // cek username
        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'username tidak ditemukan.'
            ], 404);
        }

        // cek password
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'password salah.'
            ], 401);
        }

        // cek role
        if ($user->role !== $credentials['role']) {
            return response()->json([
                'message' => "role akun tidak sesuai. anda harus masuk sebagai {$user->role}.",
            ], 401);
        }

        // buat token sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // respon sukses
        return response()->json([
            'message' => 'login berhasil',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nama' => $user->nama,
                'role' => $user->role,
            ],
            'token' => $token
        ], 200);
    }
}
