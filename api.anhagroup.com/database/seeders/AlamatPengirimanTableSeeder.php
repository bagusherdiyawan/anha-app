<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AlamatPengirimanTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('alamat_pengiriman')->delete();
        
        \DB::table('alamat_pengiriman')->insert(array (
            0 => 
            array (
                'id' => 10,
                'user_id' => 1,
                'label' => 'Rumah',
                'recipient_name' => 'Harun',
                'phone' => '09876',
                'address' => 'Sleman',
                'district' => 'Jl. Jragung 1',
                'city' => 'Yogyakarta',
                'province' => 'Yogyakarta',
                'postal_code' => '99878',
                'notes' => 'Taruh di teras',
                'default' => NULL,
                'created_at' => '2026-04-29 04:32:15',
                'updated_at' => '2026-04-30 02:44:15',
            ),
            1 => 
            array (
                'id' => 12,
                'user_id' => 1,
                'label' => 'Kantor',
                'recipient_name' => 'Harun',
                'phone' => '234324',
                'address' => 'Berbah',
                'district' => 'jl. kalisahak 28',
                'city' => 'Yogyakarta',
                'province' => 'Yogyakarta',
                'postal_code' => '5555555',
                'notes' => 'Satpam',
                'default' => 1,
                'created_at' => '2026-04-30 01:59:12',
                'updated_at' => '2026-04-30 02:44:15',
            ),
        ));
        
        
    }
}