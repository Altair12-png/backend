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
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'status_peminjaman',
    ];

    protected $dates = [
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
    ];

    public function fasilitas()
    {
        return $this->belongsTo(Fasilitas::class);
    }
}
