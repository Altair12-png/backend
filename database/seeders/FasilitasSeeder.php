<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fasilitas;

class FasilitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Fasilitas::create([
            'nama' => 'Proyektor',
            'jumlah' => 5,
            'deskripsi' => 'Proyektor kualitas tinggi untuk presentasi dan kegiatan besar.',
            'gambar_url' => 'proyektor.jpg', // Nama file aset di Flutter
        ]);

        Fasilitas::create([
            'nama' => 'Kursi Lipat',
            'jumlah' => 100,
            'deskripsi' => 'Kursi lipat besi yang ringan dan mudah dipindahkan.',
            'gambar_url' => 'kursilipat.jpg', // Nama file aset di Flutter
        ]);

        Fasilitas::create([
            'nama' => 'Sound System',
            'jumlah' => 2,
            'deskripsi' => 'Sistem suara lengkap dengan amplifier dan mikrofon nirkabel.',
            'gambar_url' => 'sound.jpg', 
        ]);
        
        Fasilitas::create([
            'nama' => 'Meja Rapat',
            'jumlah' => 15,
            'deskripsi' => 'Meja berukuran besar cocok untuk rapat tim atau pertemuan.',
            'gambar_url' => 'meja.jpg', // Nama file aset di Flutter
        ]);
    }
}