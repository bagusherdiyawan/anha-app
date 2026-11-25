<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BannersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('banners')->delete();

        \DB::table('banners')->insert(array(
            0 =>
                array(
                    'id' => 1,
                    'title' => 'Promo Kabel NYM - Hemat 10%',
                    'image_path' => 'banners/placeholder-1.jpg',
                    'description' => 'Dapatkan diskon 10% untuk pembelian kabel NYM minimal 5 gulungan',
                    'deep_link' => 'product/category/kabel-instalasi',
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => '2025-08-27 21:48:43',
                    'updated_at' => '2025-08-27 22:07:04',
                ),
            1 =>
                array(
                    'id' => 2,
                    'title' => 'Lampu LED Hemat Energi',
                    'image_path' => 'banners/placeholder-2.jpg',
                    'description' => 'Upgrade ke lampu LED dan hemat tagihan listrik hingga 80%',
                    'deep_link' => 'product/category/lampu-pencahayaan',
                    'sort_order' => 2,
                    'is_active' => true,
                    'created_at' => '2025-08-27 21:48:43',
                    'updated_at' => '2025-08-27 22:07:04',
                ),
        ));
    }
}
