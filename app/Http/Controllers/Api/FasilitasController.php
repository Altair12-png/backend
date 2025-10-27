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
     * A. List Semua Fasilitas (GET /api/fasilitas)
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
     * B. List Rekomendasi (GET /api/fasilitas/rekomendasi)
     */
    public function rekomendasi()
    {
        $rekomendasi = Fasilitas::limit(4)->inRandomOrder()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar fasilitas rekomendasi berhasil diambil',
            'data' => $rekomendasi
        ], 200);
    }

    /**
     * C. Staff: Tambah Barang/Fasilitas Baru 
     * Jika nama sudah ada, maka jumlah ditambah
     * (POST /api/fasilitas)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah nama sudah ada
        $existing = Fasilitas::where('nama', $request->nama)->first();

        if ($existing) {
            // Jika sudah ada → tambahkan jumlahnya
            $existing->jumlah += $request->jumlah;

            // Jika ada gambar baru, update gambarnya
            if ($request->hasFile('gambar')) {
                if ($existing->gambar_url && Storage::exists('public/fasilitas/' . $existing->gambar_url)) {
                    Storage::delete('public/fasilitas/' . $existing->gambar_url);
                }
                $path = $request->file('gambar')->store('public/fasilitas');
                $existing->gambar_url = basename($path);
            }

            // Update deskripsi jika dikirim
            if ($request->filled('deskripsi')) {
                $existing->deskripsi = $request->deskripsi;
            }

            $existing->save();

            return response()->json([
                'success' => true,
                'message' => "Jumlah fasilitas '{$existing->nama}' berhasil ditambah menjadi {$existing->jumlah}.",
                'data' => $existing
            ], 200);
        }

        // Kalau belum ada → buat baru
        $gambarUrl = null;
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('public/fasilitas');
            $gambarUrl = basename($path);
        }

        $fasilitas = Fasilitas::create([
            'nama' => $request->nama,
            'jumlah' => $request->jumlah,
            'deskripsi' => $request->deskripsi,
            'gambar_url' => $gambarUrl ?? $request->input('gambar_url_manual'),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Fasilitas baru '{$fasilitas->nama}' berhasil ditambahkan.",
            'data' => $fasilitas
        ], 201);
    }

    /**
     * D. Staff: Update Barang (PUT /api/fasilitas/{id})
     */
    public function update(Request $request, $id)
    {
        $fasilitas = Fasilitas::find($id);

        if (!$fasilitas) {
            return response()->json([
                'success' => false,
                'message' => 'Fasilitas tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255|unique:fasilitas,nama,' . $id,
            'jumlah' => 'sometimes|required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($fasilitas->gambar_url && Storage::exists('public/fasilitas/' . $fasilitas->gambar_url)) {
                Storage::delete('public/fasilitas/' . $fasilitas->gambar_url);
            }
            $path = $request->file('gambar')->store('public/fasilitas');
            $fasilitas->gambar_url = basename($path);
        }

        $fasilitas->update($request->only(['nama', 'jumlah', 'deskripsi']));

        return response()->json([
            'success' => true,
            'message' => 'Fasilitas berhasil diperbarui.',
            'data' => $fasilitas
        ], 200);
    }

    /**
     * E. Staff: Hapus Barang (DELETE /api/fasilitas/{id})
     */
    public function destroy($id)
    {
        $fasilitas = Fasilitas::find($id);

        if (!$fasilitas) {
            return response()->json([
                'success' => false,
                'message' => 'Fasilitas tidak ditemukan.',
            ], 404);
        }

        // Hapus gambar di storage jika ada
        if ($fasilitas->gambar_url && Storage::exists('public/fasilitas/' . $fasilitas->gambar_url)) {
            Storage::delete('public/fasilitas/' . $fasilitas->gambar_url);
        }

        $fasilitas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fasilitas berhasil dihapus.',
        ], 200);
    }
}

