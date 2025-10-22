<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FasilitasController extends Controller
{
    /**
     * A. List Semua Fasilitas (GET /api/fasilitas) - Untuk ListFasilitasPage
     */
    public function index()
    {
        $fasilitas = Fasilitas::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar semua fasilitas berhasil diambil',
            'data' => $fasilitas
        ], 200);
    }

    /**
     * B. List Rekomendasi (GET /api/fasilitas/rekomendasi) - Untuk Dashboard
     */
    public function rekomendasi()
    {
        // Mengambil 4 item secara acak sebagai rekomendasi
        $rekomendasi = Fasilitas::limit(4)->inRandomOrder()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar fasilitas rekomendasi berhasil diambil',
            'data' => $rekomendasi
        ], 200);
    }

    /**
     * Staff: Menambah Barang/Fasilitas Baru (POST /api/fasilitas)
     */
    public function store(Request $request)
    {
        // CATATAN: Middleware autentikasi dan otorisasi (hanya staff) harus diterapkan di routing.

        $validator = Validator::make($request->all(), [
            // Nama harus unik agar tidak ada fasilitas yang diduplikasi
            'nama' => 'required|string|max:255|unique:fasilitas,nama', 
            'jumlah' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            // Gambar Barang (opsional) - max 2MB
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Pastikan nama unik dan format gambar benar.',
                'errors' => $validator->errors()
            ], 422);
        }

        $gambarUrl = null;
        
        // Handle File Upload (jika ada gambar)
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('public/fasilitas');
            // Hanya simpan nama file/path relatif ke database
            $gambarUrl = basename($path); 
        }

        // Simpan Fasilitas Baru
        $fasilitas = Fasilitas::create([
            'nama' => $request->nama,
            'jumlah' => $request->jumlah,
            'deskripsi' => $request->deskripsi,
            // Jika tidak ada upload, gambar_url akan berisi nama file aset statis jika diisi manual,
            // atau null jika menggunakan nama file dari hasil upload.
            'gambar_url' => $gambarUrl ?? $request->input('gambar_url_manual'), 
        ]);

        // Respons untuk SnackBar
        return response()->json([
            'success' => true,
            'message' => "Berhasil: {$fasilitas->nama} ({$fasilitas->jumlah} buah)",
            'data' => $fasilitas
        ], 201);
    }
}