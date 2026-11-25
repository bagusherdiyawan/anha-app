<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class KategoriBarangsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('kategori_barangs')->delete();
        
        \DB::table('kategori_barangs')->insert(array (
            0 => 
            array (
                'id' => 107,
                'kode' => '060002',
                'nama' => 'Stop Kontak',
                'deskripsi' => 'Tempat atau titik penghubung arus listrik pada tembok atau dudukan, di mana steker ditancapkan untuk mengambil daya.',
                'foto' => 'photos_kategori/88slUQLIEy7nMY5yEckoQxNeMG7tVEKc5vjQ90ZQ.png',
                'landing' => 1,
                'status_active' => 1,
                'created_at' => '2025-09-09 11:19:37',
                'updated_at' => '2025-09-09 13:48:12',
            ),
            1 => 
            array (
                'id' => 109,
                'kode' => '060001',
                'nama' => 'Kabel Listrik',
            'deskripsi' => 'Berbagai jenis kabel untuk instalasi rumah (NYA, NYM, NYY) dan kabel roll/ekstensi.',
                'foto' => 'photos_kategori/kkWHrXWtVb9LySII1AANjW1VjagCDG6wOcag7DFk.png',
                'landing' => 1,
                'status_active' => 1,
                'created_at' => '2025-09-10 20:59:30',
                'updated_at' => '2025-09-13 17:43:47',
            ),
            2 => 
            array (
                'id' => 114,
                'kode' => '060003',
                'nama' => 'Pencahayaan',
                'deskripsi' => 'Berbagai jenis sumber cahaya, seperti lampu LED, lampu pijar, downlight, hingga lampu hias/tl',
                'foto' => 'photos_kategori/88slUQLIEy7nMY5yEckoQxNeMG7tVEKc5vjQ90ZQ.png',
                'landing' => 1,
                'status_active' => 1,
                'created_at' => '2025-09-09 11:19:37',
                'updated_at' => '2025-09-09 13:48:12',
            ),
            3 => 
            array (
                'id' => 115,
                'kode' => '060004',
                'nama' => 'Pengaman & Panel',
            'deskripsi' => 'Miniature Circuit Breaker (MCB), sekring (fuse), boks MCB, dan komponen panel listrik.',
                'foto' => 'photos_kategori/88slUQLIEy7nMY5yEckoQxNeMG7tVEKc5vjQ90ZQ.png',
                'landing' => 1,
                'status_active' => 1,
                'created_at' => '2025-09-09 11:19:37',
                'updated_at' => '2025-09-09 13:48:12',
            ),
            4 => 
            array (
                'id' => 116,
                'kode' => '060005',
                'nama' => 'Fitting Lampu',
                'deskripsi' => 'Dudukan tempat lampu dipasang agar lampu mendapatkan aliran listrik. Jenisnya ada fitting gantung, plafon, atau fitting khusus.',
                'foto' => 'photos_kategori/88slUQLIEy7nMY5yEckoQxNeMG7tVEKc5vjQ90ZQ.png',
                'landing' => 1,
                'status_active' => 1,
                'created_at' => '2025-09-09 11:19:37',
                'updated_at' => '2025-09-09 13:48:12',
            ),
            5 => 
            array (
                'id' => 118,
                'kode' => '060006',
                'nama' => 'Steker',
                'deskripsi' => 'Alat yang dipasang di ujung kabel peralatan elektronik untuk ditancapkan ke stop kontak agar alat tersebut mendapatkan aliran listrik.',
                'foto' => 'photos_kategori/88slUQLIEy7nMY5yEckoQxNeMG7tVEKc5vjQ90ZQ.png',
                'landing' => 1,
                'status_active' => 1,
                'created_at' => '2025-09-09 11:19:37',
                'updated_at' => '2025-09-09 13:48:12',
            ),
            6 => 
            array (
                'id' => 119,
                'kode' => '060007',
                'nama' => 'Sakelar',
                'deskripsi' => 'Alat yang berfungsi untuk memutuskan dan menyambungkan aliran listrik, biasanya digunakan untuk menyalakan/mematikan lampu atau kipas angin.',
                'foto' => 'photos_kategori/88slUQLIEy7nMY5yEckoQxNeMG7tVEKc5vjQ90ZQ.png',
                'landing' => 1,
                'status_active' => 1,
                'created_at' => '2025-09-09 11:19:37',
                'updated_at' => '2025-09-09 13:48:12',
            ),
            7 => 
            array (
                'id' => 120,
                'kode' => '060008',
                'nama' => 'Circuit Breaker',
            'deskripsi' => 'Miniature Circuit Breaker (MCB) berfungsi untuk membatasi daya dan melindungi instalasi dari beban berlebih atau korsleting listrik.',
                'foto' => 'photos_kategori/88slUQLIEy7nMY5yEckoQxNeMG7tVEKc5vjQ90ZQ.png',
                'landing' => 1,
                'status_active' => 1,
                'created_at' => '2025-09-09 11:19:37',
                'updated_at' => '2025-09-09 13:48:12',
            ),
        ));
        
        
    }
}