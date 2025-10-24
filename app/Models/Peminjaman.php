<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

protected $table = 'peminjaman';

protected $fillable = [
    'fasilitas_id',
    'nama_peminjam',
    'jumlah_pinjam',
    'alasan',
    'tanggal_pinjam',
    // Kolom yang digunakan oleh Backend
    'tanggal_kembali_rencana', 
    'tanggal_kembali_aktual',
    'status_peminjaman',
];

protected $dates = [
    'tanggal_pinjam',
    'tanggal_kembali_rencana', // Diperhatikan
    'tanggal_kembali_aktual',
];

public function index()
{
    $data = Peminjaman::with('fasilitas')->orderByDesc('created_at')->get();
    return response()->json([
        'success' => true,
        'message' => 'Daftar semua peminjaman',
        'data' => $data,
    ]);
} 
public function fasilitas()
{
    return $this->belongsTo(Fasilitas::class);
}
}