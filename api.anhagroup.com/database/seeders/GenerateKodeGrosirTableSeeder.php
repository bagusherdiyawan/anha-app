<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GenerateKodeGrosirTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('generate_kode_grosir')->delete();
        
        \DB::table('generate_kode_grosir')->insert(array (
            0 => 
            array (
                'id' => 1,
                'kode_grosir' => '1040503',
                'created_at' => '2026-05-04 22:43:03',
                'updated_at' => '2026-05-04 22:43:03',
            ),
            1 => 
            array (
                'id' => 2,
                'kode_grosir' => '2040516',
                'created_at' => '2026-05-04 22:43:16',
                'updated_at' => '2026-05-04 22:43:16',
            ),
            2 => 
            array (
                'id' => 3,
                'kode_grosir' => '3040532',
                'created_at' => '2026-05-04 22:43:32',
                'updated_at' => '2026-05-04 22:43:32',
            ),
            3 => 
            array (
                'id' => 4,
                'kode_grosir' => '4060548',
                'created_at' => '2026-05-06 16:45:48',
                'updated_at' => '2026-05-06 16:45:48',
            ),
        ));
        
        
    }
}