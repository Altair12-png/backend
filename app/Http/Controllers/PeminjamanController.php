<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// Jika kamu ingin menggunakan ID user yang sedang login (Auth::id()), 
// tambahkan 'use Illuminate\Support\Facades\Auth;'
// Tapi berdasarkan permintaan validasi, user_id diasumsikan dikirim via request.

class PeminjamanController extends Controller
{
    // warga ajukan peminjaman
    public function store(Request $request)
    {
        $request->validate([
            'fasilitas_id' => 'required|exists:fasilitas,id',
            'user_id' => 'required|exists:users,id', // <-- DITAMBAHKAN
            'nama_peminjam' => 'required|string|max:255',
            'jumlah_pinjam' => 'required|integer|min:1',
            'alasan' => 'required|string',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after_or_equal:tanggal_pinjam',
        ]);

    $fasilitas = Fasilitas::find($request->fasilitas_id);

    if (!$fasilitas || $fasilitas->jumlah < $request->jumlah_pinjam) {
        return response()->json([
            'message' => 'stok fasilitas tidak mencukupi atau fasilitas tidak ditemukan.'
        ], 400);
    }

    $peminjaman = Peminjaman::create([
        'fasilitas_id' => $request->fasilitas_id,
        'user_id' => $request->user_id, // <-- DITAMBAHKAN
        'nama_peminjam' => $request->nama_peminjam,
        'jumlah_pinjam' => $request->jumlah_pinjam,
        'alasan' => $request->alasan,
        'tanggal_pinjam' => $request->tanggal_pinjam,
        'tanggal_kembali_rencana' => $request->tanggal_kembali_rencana,
        'status_peminjaman' => 'Diajukan',
    ]);

    return response()->json([
        'message' => 'pengajuan berhasil diajukan.',
        'data' => $peminjaman
    ], 201);
}

    // rt/rw setujui pengajuan
    public function setujui($id)
    {
        $peminjaman = Peminjaman::find($id);

        if (!$peminjaman) {
            return response()->json(['message' => 'peminjaman tidak ditemukan.'], 404);
        }
        if ($peminjaman->status_peminjaman !== 'Diajukan') {
            return response()->json(['message' => 'hanya pengajuan yang bisa disetujui.'], 400);
        }

        $peminjaman->status_peminjaman = 'Disetujui';
        $peminjaman->save();

        return response()->json(['message' => 'pengajuan berhasil disetujui.'], 200);
    }

    // rt/rw tolak pengajuan
    public function tolak($id)
    {
        $peminjaman = Peminjaman::find($id);

        if (!$peminjaman) {
            return response()->json(['message' => 'peminjaman tidak ditemukan.'], 404);
        }
        if ($peminjaman->status_peminjaman !== 'Diajukan') {
            return response()->json(['message' => 'hanya pengajuan yang bisa ditolak.'], 400);
        }

        $peminjaman->status_peminjaman = 'Ditolak';
        $peminjaman->save();

        return response()->json(['message' => 'pengajuan berhasil ditolak.'], 200);
    }

    // staff serahkan barang
    public function serahkan($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::lockForUpdate()->find($id);

            if (!$peminjaman) {
                DB::rollBack();
                return response()->json(['message' => 'peminjaman tidak ditemukan.'], 404);
            }
            if ($peminjaman->status_peminjaman !== 'Disetujui') {
                DB::rollBack();
                return response()->json(['message' => 'barang belum disetujui oleh rt/rw.'], 400);
            }

            $fasilitas = Fasilitas::lockForUpdate()->find($peminjaman->fasilitas_id);

            if ($fasilitas->jumlah < $peminjaman->jumlah_pinjam) {
                DB::rollBack();
                return response()->json(['message' => 'stok tidak cukup saat penyerahan.'], 400);
            }

            $fasilitas->jumlah -= $peminjaman->jumlah_pinjam;
            $fasilitas->save();

            $peminjaman->status_peminjaman = 'Diserahkan';
            $peminjaman->save();

            DB::commit();
            return response()->json(['message' => 'barang berhasil diserahkan dan stok berkurang.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'terjadi kesalahan saat penyerahan.'], 500);
        }
    }

    // staff kembalikan barang
    public function kembalikan($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::lockForUpdate()->find($id);

            if (!$peminjaman) {
                DB::rollBack();
                return response()->json(['message' => 'peminjaman tidak ditemukan.'], 404);
            }
            if ($peminjaman->status_peminjaman !== 'Diserahkan') {
                DB::rollBack();
                return response()->json(['message' => 'barang belum diserahkan atau sudah selesai.'], 400);
            }

            $tanggalRencana = Carbon::parse($peminjaman->tanggal_kembali_rencana)->startOfDay();
            $today = Carbon::now()->startOfDay();

            if ($today->lt($tanggalRencana)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'pengembalian belum bisa dilakukan sebelum tanggal rencana (' . $tanggalRencana->format('d-m-Y') . ').'
                ], 400);
            }

            $fasilitas = Fasilitas::lockForUpdate()->find($peminjaman->fasilitas_id);
            $fasilitas->jumlah += $peminjaman->jumlah_pinjam;
            $fasilitas->save();

            $peminjaman->status_peminjaman = 'Selesai';
            $peminjaman->tanggal_kembali_aktual = Carbon::now();
            $peminjaman->save();

            DB::commit();
            return response()->json(['message' => 'barang berhasil dikembalikan dan stok bertambah.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'terjadi kesalahan saat pengembalian.'], 500);
        }
    }

    public function index()
    {
        $data = Peminjaman::with('fasilitas')->orderByDesc('created_at')->get();
            return response()->json([
            'success' => true,
            'message' => 'Daftar semua peminjaman',
            'data' => $data,
    ]);
    } 

    public function getByUser($userId)
{
    $data = Peminjaman::with('fasilitas')
        ->where('user_id', $userId) // <- Ini sudah benar
        ->orderByDesc('created_at')
        ->get();

if ($data->isEmpty()) {
    return response()->json([
        'success' => false,
        'message' => 'Belum ada peminjaman untuk user ini.',
        'data' => [],
    ], 404);
    }
    return response()->json([
        'success' => true,
        'message' => 'Daftar peminjaman oleh user',
        'data' => $data,
        ]);
    }

    
}