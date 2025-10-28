<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FasilitasController extends Controller
{
    /** A. List Semua Fasilitas */
    public function index()
    {
        $fasilitas = Fasilitas::all()->map(function ($item) {
            if ($item->gambar_url && !str_starts_with($item->gambar_url, 'http')) {
                $item->gambar_url = asset('storage/fasilitas/' . $item->gambar_url);
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua fasilitas berhasil diambil',
            'data' => $fasilitas
        ], 200);
    }

    /** B. List Rekomendasi */
    public function rekomendasi()
    {
        $rekomendasi = Fasilitas::limit(4)->inRandomOrder()->get()->map(function ($item) {
            if ($item->gambar_url && !str_starts_with($item->gambar_url, 'http')) {
                $item->gambar_url = asset('storage/fasilitas/' . $item->gambar_url);
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar fasilitas rekomendasi berhasil diambil',
            'data' => $rekomendasi
        ], 200);
    }

    /** C. Tambah Fasilitas / Update Jika Sudah Ada */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $existing = Fasilitas::where('nama', $request->nama)->first();

        if ($existing) {
            $existing->jumlah += $request->jumlah;
            if ($request->hasFile('gambar')) {
                if ($existing->gambar_url && Storage::exists('public/fasilitas/' . $existing->gambar_url)) {
                    Storage::delete('public/fasilitas/' . $existing->gambar_url);
                }
                $path = $request->file('gambar')->store('public/fasilitas');
                $existing->gambar_url = basename($path);
            }
            if ($request->filled('deskripsi')) {
                $existing->deskripsi = $request->deskripsi;
            }
            $existing->save();

            // pastikan gambar_url jadi URL penuh
            if ($existing->gambar_url && !str_starts_with($existing->gambar_url, 'http')) {
                $existing->gambar_url = asset('storage/fasilitas/' . $existing->gambar_url);
            }

            return response()->json([
                'success' => true,
                'message' => "Jumlah fasilitas '{$existing->nama}' berhasil ditambah menjadi {$existing->jumlah}.",
                'data' => $existing
            ], 200);
        }

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

        if ($fasilitas->gambar_url && !str_starts_with($fasilitas->gambar_url, 'http')) {
            $fasilitas->gambar_url = asset('storage/fasilitas/' . $fasilitas->gambar_url);
        }

        return response()->json([
            'success' => true,
            'message' => "Fasilitas baru '{$fasilitas->nama}' berhasil ditambahkan.",
            'data' => $fasilitas
        ], 201);
    }

    /** D. Update Fasilitas */
    public function update(Request $request, $id)
    {
        $fasilitas = Fasilitas::find($id);
        if (!$fasilitas) {
            return response()->json(['success' => false, 'message' => 'Fasilitas tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255|unique:fasilitas,nama,' . $id,
            'jumlah' => 'sometimes|required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('gambar')) {
            if ($fasilitas->gambar_url && Storage::exists('public/fasilitas/' . $fasilitas->gambar_url)) {
                Storage::delete('public/fasilitas/' . $fasilitas->gambar_url);
            }
            $path = $request->file('gambar')->store('public/fasilitas');
            $fasilitas->gambar_url = basename($path);
        }

        $fasilitas->update($request->only(['nama', 'jumlah', 'deskripsi']));

        if ($fasilitas->gambar_url && !str_starts_with($fasilitas->gambar_url, 'http')) {
            $fasilitas->gambar_url = asset('storage/fasilitas/' . $fasilitas->gambar_url);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fasilitas berhasil diperbarui.',
            'data' => $fasilitas
        ], 200);
    }

    /** E. Hapus Fasilitas */
    public function destroy($id)
    {
        $fasilitas = Fasilitas::find($id);
        if (!$fasilitas) {
            return response()->json(['success' => false, 'message' => 'Fasilitas tidak ditemukan.'], 404);
        }

        if ($fasilitas->gambar_url && Storage::exists('public/fasilitas/' . $fasilitas->gambar_url)) {
            Storage::delete('public/fasilitas/' . $fasilitas->gambar_url);
        }

        $fasilitas->delete();
        return response()->json(['success' => true, 'message' => 'Fasilitas berhasil dihapus.'], 200);
    }
}
