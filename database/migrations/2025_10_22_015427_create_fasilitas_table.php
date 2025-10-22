<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fasilitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique(); // Nama Fasilitas 
            $table->integer('jumlah')->default(0); // Stok barang yang tersedia
            $table->text('deskripsi')->nullable(); 
            // Wajib sesuai nama file aset statis di Flutter . Nullable jika gambar opsional.
            $table->string('gambar_url')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fasilitas');
    }
};