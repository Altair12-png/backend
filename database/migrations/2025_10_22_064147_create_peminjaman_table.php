<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fasilitas_id')->constrained('fasilitas')->onDelete('cascade');

            // data peminjam
            $table->string('nama_peminjam');
            $table->integer('jumlah_pinjam');
            $table->text('alasan');

            // detail waktu
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali_rencana');
            $table->dateTime('tanggal_kembali_aktual')->nullable();

            // status
            $table->enum('status_peminjaman', [
                'Diajukan',
                'Disetujui',
                'Ditolak',
                'Diserahkan',
                'Selesai'
            ])->default('Diajukan');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
