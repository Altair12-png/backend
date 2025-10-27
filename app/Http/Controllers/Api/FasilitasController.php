<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fasilitas;
use Illuminate\Support\Facades\Storage;

class FasilitasController extends Controller
{
    // ✅ Ambil semua data fasilitas
    public function index()
    {
        $fasilitas = Fasilitas::all();
        return response()->json($fasilitas);
    }

    // ✅ Tambah fasilitas baru atau update stok jika nama sama
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'stok' => 'required|integer|min:1',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Cek apakah fasilitas dengan nama yang sama sudah ada
        $existingFasilitas = Fasilitas::where('nama', $request->nama)->first();

        if ($existingFasilitas) {
            // Jika ada, tambahkan stoknya
            $existingFasilitas->stok += $request->stok;
            $existingFasilitas->save();

            return response()->json([
                'message' => 'Stok fasilitas berhasil diperbarui',
                'data' => $existingFasilitas
            ]);
        }

        // Simpan gambar jika ada
        $gambarUrl = null;
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('public/fasilitas');
            $gambarUrl = url('storage/fasilitas/' . basename($path)); // 🔥 simpan URL lengkap
        }

        $fasilitas = Fasilitas::create([
            'nama' => $request->nama,
            'stok' => $request->stok,
            'gambar_url' => $gambarUrl,
        ]);

        return response()->json([
            'message' => 'Fasilitas berhasil ditambahkan',
            'data' => $fasilitas
        ]);
    }

    // ✅ Update data fasilitas
    public function update(Request $request, $id)
    {
        $fasilitas = Fasilitas::findOrFail($id);

        $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'stok' => 'sometimes|required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->has('nama')) {
            $fasilitas->nama = $request->nama;
        }

        if ($request->has('stok')) {
            $fasilitas->stok = $request->stok;
        }

        // 🔥 Update gambar jika ada file baru
        if ($request->hasFile('gambar')) {
            if (
                $fasilitas->gambar_url &&
                Storage::exists('public/fasilitas/' . basename($fasilitas->gambar_url))
            ) {
                Storage::delete('public/fasilitas/' . basename($fasilitas->gambar_url));
            }

            $path = $request->file('gambar')->store('public/fasilitas');
            $fasilitas->gambar_url = url('storage/fasilitas/' . basename($path));
        }

        $fasilitas->save();

        return response()->json([
            'message' => 'Fasilitas berhasil diperbarui',
            'data' => $fasilitas
        ]);
    }

    // ✅ Hapus data fasilitas
    public function destroy($id)
    {
        $fasilitas = Fasilitas::findOrFail($id);

        if (
            $fasilitas->gambar_url &&
            Storage::exists('public/fasilitas/' . basename($fasilitas->gambar_url))
        ) {
            Storage::delete('public/fasilitas/' . basename($fasilitas->gambar_url));
        }

        $fasilitas->delete();

        return response()->json([
            'message' => 'Fasilitas berhasil dihapus'
        ]);
    }
}