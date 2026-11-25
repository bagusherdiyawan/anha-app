<?php

namespace Database\Seeders;

use App\Models\Toko;
use Illuminate\Database\Seeder;

class TokoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Toko::create([
            'nama_toko' => 'Anha Store',
            'alamat' => 'Yogyakarta',
            'nomor_telepon' => '08123456789',
            'logo' => null,
        ]);
    }
}
