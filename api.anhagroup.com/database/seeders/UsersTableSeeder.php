<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->delete();

        \DB::table('users')->insert(array(
            0 =>
                array(
                    'id' => 1,
                    'name' => 'Danang',
                    'email' => 'laharun00@gmail.com',
                    'phone' => '089624866321',
                    'address' => 'yogyakarta',
                    'password' => '$2y$12$36.5L/sG.mhrQCBK3r/YneyBpStfL3BwcFeF3D5WAbCWAidfUSoG6',
                    'role' => 'ritel',
                    'kode_grosir' => NULL,
                    'kode_otp' => '495911',
                    'email_verified_at' => '2026-04-29 04:20:36',
                    'photo_url' => 'photos_url/0Edy72fza0yl8qTPVb5KlTUjKGW3fs3ap64BwUwj.png',
                    'remember_token' => NULL,
                    'created_at' => '2026-04-29 04:12:23',
                    'updated_at' => '2026-04-30 07:44:11',
                ),
            1 =>
                array(
                    'id' => 2,
                    'name' => 'harun',
                    'email' => 'laharun92@gmail.com',
                    'phone' => '0895456888654',
                    'address' => 'kanoman',
                    'password' => '$2y$12$/8rZrFQzcb.m7fMK1ErM3O/w2eO7MANpfvy/KTc6nzDAcOnGNL2By',
                    'role' => 'grosir',
                    'kode_grosir' => NULL,
                    'kode_otp' => '465516',
                    'email_verified_at' => NULL,
                    'photo_url' => NULL,
                    'remember_token' => NULL,
                    'created_at' => '2026-04-30 04:10:31',
                    'updated_at' => '2026-04-30 04:10:31',
                ),
            2 =>
                array(
                    'id' => 3,
                    'name' => 'Tes User Grosir',
                    'email' => 'khansa.abdilla00@gmail.com',
                    'phone' => '0895456888653',
                    'address' => 'yogyakarta',
                    'password' => '$2y$12$LhMuic1SQFyRUktTlS4uvuXYP7jCyW9xYlFI7ZvZSk4WXsKRT/XYy',
                    'role' => 'admin',
                    'kode_grosir' => 4060548,
                    'kode_otp' => '623771',
                    'email_verified_at' => '2026-05-07 15:09:07',
                    'photo_url' => 'photos_url/lO4Y8j3wl8nB4h3iRGh2X7aN8sW25EgaIeHyhzAz.png',
                    'remember_token' => NULL,
                    'created_at' => '2026-05-07 15:06:42',
                    'updated_at' => '2026-05-07 15:21:55',
                ),
        ));
    }
}
