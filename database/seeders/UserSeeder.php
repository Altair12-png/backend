<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk tabel users.
     */
    public function run(): void
    {
        // Akun Warga
        DB::table('users')->insert([
            'username' => 'warga',
            'password' => Hash::make('123'),
            'nama' => 'Budi Warga',
            'role' => 'warga',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Akun Staff
        DB::table('users')->insert([
            'username' => 'staff',
            'password' => Hash::make('123'),
            'nama' => 'Siti Admin',
            'role' => 'staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Akun RT/RW
        DB::table('users')->insert([
            'username' => 'rtrw',
            'password' => Hash::make('123'),
            'nama' => 'Pak RT RW',
            'role' => 'rtrw',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
