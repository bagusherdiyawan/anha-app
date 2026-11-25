<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BarangsFotoTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('barangs_foto')->delete();
        
        \DB::table('barangs_foto')->insert(array (
            0 => 
            array (
                'id' => 23,
                'id_barang' => 23,
                'url_foto_barang' => 'photos_barang/1JQXNQeyGwbU5qwPfThj6Dsk4zfJoLR8icfkdKCK.png',
                'created_at' => '2026-05-02 07:55:38',
                'updated_at' => '2026-05-02 07:55:38',
            ),
            1 => 
            array (
                'id' => 33,
                'id_barang' => 22,
                'url_foto_barang' => 'photos_barang/4QODYwlJ2qSeDj3Bd6PsmOgMxaIWGQZn6CIsMOeB.webp',
                'created_at' => '2026-05-03 01:16:09',
                'updated_at' => '2026-05-03 01:16:09',
            ),
        ));
        
        
    }
}