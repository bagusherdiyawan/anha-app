<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UsersTableSeeder::class);
        $this->call(TokoSeeder::class);
        $this->call(KategoriBarangsTableSeeder::class);
        $this->call(AlamatPengirimanTableSeeder::class);
        $this->call(BannersTableSeeder::class);
        $this->call(BarangsTableSeeder::class);
        $this->call(BarangsFotoTableSeeder::class);
        $this->call(BarangsHrgGrosirTableSeeder::class);
        $this->call(BarangsHrgRitelTableSeeder::class);
        $this->call(GenerateKodeGrosirTableSeeder::class);
        $this->call(MigrationsTableSeeder::class);
        $this->call(SystemConfigsTableSeeder::class);
        $this->call(RekeningTokosTableSeeder::class);
    }
}
