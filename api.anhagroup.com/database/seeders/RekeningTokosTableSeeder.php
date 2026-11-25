<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RekeningTokosTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('rekening_tokos')->delete();

        \DB::table('rekening_tokos')->insert(array(
            0 =>
                array(
                    'id' => 1,
                    'nama' => 'COD',
                    'nomor' => '-',
                    'foto' => NULL,
                    'status_active' => 1,
                    'type' => 'cod',
                    'keterangan' => NULL,
                    'created_at' => '2026-05-12 17:04:35',
                    'updated_at' => NULL,
                ),
            1 =>
                array(
                    'id' => 2,
                    'nama' => 'Bank Mandiri',
                    'nomor' => '1234567890',
                    'foto' => NULL,
                    'status_active' => 1,
                    'type' => 'transfer',
                    'keterangan' => NULL,
                    'created_at' => '2026-05-12 17:04:35',
                    'updated_at' => NULL,
                ),
            2 =>
                array(
                    'id' => 3,
                    'nama' => 'QRIS',
                    'nomor' => '1234567890',
                    'foto' => NULL,
                    'status_active' => 1,
                    'type' => 'wallet',
                    'keterangan' => NULL,
                    'created_at' => '2026-05-12 17:04:35',
                    'updated_at' => NULL,
                ),
        ));
    }
}
