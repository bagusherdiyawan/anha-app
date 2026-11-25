<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PembagiPointsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('pembagi_points')->delete();
        
        \DB::table('pembagi_points')->insert(array (
            0 => 
            array (
                'id' => 1,
                'nominal' => '40000.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}