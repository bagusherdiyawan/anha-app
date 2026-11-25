<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BarangsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('barangs')->delete();
        
        \DB::table('barangs')->insert(array (
            0 => 
            array (
                'id' => 22,
            'nama_barang' => 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
                'kode_barang' => NULL,
                'barcode' => NULL,
                'satuan' => 'biji',
            'deskripsi' => 'Kabel instalasi rumah, 2 lapisan isolasi (aman untuk dinding/plafon).',
                'kode_kategori' => '060001',
                'rating' => '5',
                'created_at' => '2026-05-02 05:38:29',
                'updated_at' => '2026-05-03 02:44:52',
            ),
            1 => 
            array (
                'id' => 23,
            'nama_barang' => 'Kabel Eterna NYM 3x2.5mm (50 Meter)',
                'kode_barang' => NULL,
                'barcode' => NULL,
                'satuan' => 'biji',
            'deskripsi' => 'Kabel instalasi utama/stop kontak, 3 inti (fasa, netral, ground).',
                'kode_kategori' => '060001',
                'rating' => '4',
                'created_at' => '2026-05-02 07:55:38',
                'updated_at' => '2026-05-02 07:55:38',
            ),
            2 => 
            array (
                'id' => 24,
            'nama_barang' => 'Kabel Supreme NYA 1x2.5mm (100 Meter)',
                'kode_barang' => NULL,
                'barcode' => NULL,
                'satuan' => 'biji',
                'deskripsi' => 'Kabel tunggal untuk instalasi di dalam pipa conduit.',
                'kode_kategori' => '060001',
                'rating' => '3',
                'created_at' => '2026-05-02 07:55:38',
                'updated_at' => '2026-05-02 07:55:38',
            ),
            3 => 
            array (
                'id' => 25,
            'nama_barang' => '[Uticon ST-1482 (4 Lubang + Kabel)]',
                'kode_barang' => NULL,
                'barcode' => NULL,
                'satuan' => 'biji',
            'deskripsi' => 'Salah satu merk terbaik, aman (SNI), bahan polikarbonat tahan panas, dilengkapi kabel panjang (tersedia berbagai ukuran).
',
            'kode_kategori' => '060002',
            'rating' => '2',
            'created_at' => '2026-05-02 07:55:38',
            'updated_at' => '2026-05-02 07:55:38',
        ),
        4 => 
        array (
            'id' => 26,
        'nama_barang' => '[Broco 15851 B (5 Lubang + Kabel)]',
            'kode_barang' => NULL,
            'barcode' => NULL,
            'satuan' => 'biji',
        'deskripsi' => 'Merk Broco terkenal kokoh, tahan panas, memiliki arde (grounding) yang baik.
',
        'kode_kategori' => '060002',
        'rating' => '1',
        'created_at' => '2026-05-02 07:55:38',
        'updated_at' => '2026-05-02 07:55:38',
    ),
    5 => 
    array (
        'id' => 27,
    'nama_barang' => '[Kenmaster Kabel Roll KF-BG-4 (5 Meter)]',
        'kode_barang' => NULL,
        'barcode' => NULL,
        'satuan' => 'biji',
    'deskripsi' => 'Model gulungan (roll), 4 lubang, cocok untuk pekerjaan teknis atau rumah tangga yang butuh jangkauan jauh.
',
    'kode_kategori' => '060002',
    'rating' => '0',
    'created_at' => '2026-05-02 07:55:38',
    'updated_at' => '2026-05-02 07:55:38',
),
6 => 
array (
    'id' => 28,
    'nama_barang' => 'UTICON StopKontak Arde + Saklar Lampu 3 Lubang',
    'kode_barang' => NULL,
    'barcode' => NULL,
    'satuan' => 'biji',
    'deskripsi' => 'Stopkontak tipe terminal, SNI, saklar lampu terintegrasi, tersedia 3-6 lubang.',
    'kode_kategori' => '060003',
    'rating' => NULL,
    'created_at' => '2026-05-02 07:55:38',
    'updated_at' => '2026-05-02 07:55:38',
),
7 => 
array (
    'id' => 29,
    'nama_barang' => 'Panasonic Saklar Seri LAMPU + Stopkontak Tanam',
    'kode_barang' => NULL,
    'barcode' => NULL,
    'satuan' => 'biji',
    'deskripsi' => 'Komponen tanam di dinding, cocok untuk spotlight atau lampu dinding, standar Panasonic.
',
    'kode_kategori' => '060003',
    'rating' => NULL,
    'created_at' => '2026-05-02 07:55:38',
    'updated_at' => '2026-05-02 07:55:38',
),
8 => 
array (
    'id' => 30,
    'nama_barang' => 'Stopkontak Multifungsi dengan USB dan Lampu Malam',
    'kode_barang' => NULL,
    'barcode' => NULL,
    'satuan' => 'biji',
    'deskripsi' => 'Model 9 in 1, 3 soket AC, 2 USB, 2 Type-C, dilengkapi LED night light dan jam alarm.
',
    'kode_kategori' => '060003',
    'rating' => NULL,
    'created_at' => '2026-05-02 07:55:38',
    'updated_at' => '2026-05-02 07:55:38',
),
9 => 
array (
    'id' => 31,
'nama_barang' => 'Miniature Circuit Breaker (MCB)',
    'kode_barang' => NULL,
    'barcode' => NULL,
    'satuan' => 'biji',
'deskripsi' => 'Berfungsi sebagai pengaman arus lebih (overload) dan hubung singkat (korsleting) untuk beban kecil/menengah (rumah, ruko, kantor).
',
'kode_kategori' => '060004',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
10 => 
array (
'id' => 32,
'nama_barang' => 'Molded Case Circuit Breaker (MCCB)',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Pengaman untuk arus besar (industri atau panel utama bangunan).',
'kode_kategori' => '060004',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
11 => 
array (
'id' => 33,
'nama_barang' => 'Earth Leakage Circuit Breaker (ELCB/RCBO)',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Proteksi terhadap kebocoran arus listrik, mencegah sengatan listrik manusia dan kebakaran.
',
'kode_kategori' => '060004',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
12 => 
array (
'id' => 34,
'nama_barang' => 'Fitting Plafon',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Tipe E27 (ukuran standar rumah), bahan keramik/plastik tebal, aman, dan tahan panas',
'kode_kategori' => '060005',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
13 => 
array (
'id' => 35,
'nama_barang' => 'Fitting Gantung',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Dilengkapi kabel (panjang bervariasi) dan saklar on/off agar mudah dinyalakan/dimatikan',
'kode_kategori' => '060005',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
14 => 
array (
'id' => 36,
'nama_barang' => 'Fitting Kombinasi',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Satu titik dudukan lampu E27 dan dilengkapi 1 atau 2 colokan listrik tambahan (steker)',
'kode_kategori' => '060005',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
15 => 
array (
'id' => 37,
'nama_barang' => 'Steker Arde Bulat',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Steker standar dengan tanah (grounding) berbentuk bulat. Aman untuk penggunaan alat elektronik rumah tangga sehari-hari seperti kipas angin, TV, dan setrika. Sudah SNI.
',
'kode_kategori' => '060006',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
16 => 
array (
'id' => 38,
'nama_barang' => 'Steker T',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Memungkinkan satu stop kontak dinding digunakan untuk 3 perangkat sekaligus. Memiliki arde untuk keamanan.
',
'kode_kategori' => '060006',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
17 => 
array (
'id' => 39,
'nama_barang' => 'Steker Gepeng',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Steker dua pin tipis/pipih. Sering digunakan untuk perangkat dengan daya kecil seperti charger HP atau lampu tidur.
',
'kode_kategori' => '060006',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
18 => 
array (
'id' => 40,
'nama_barang' => 'Saklar Tunggal',
'kode_barang' => '123',
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Digunakan untuk memutus/menghubungkan satu aliran listrik ke satu lampu atau satu perangkat elektronik. Ditanam dalam tembok (inbow).
',
'kode_kategori' => '060007',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
19 => 
array (
'id' => 41,
'nama_barang' => 'Saklar Seri',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Saklar dengan dua tombol dalam satu panel, digunakan untuk mengontrol dua lampu atau dua kelompok lampu secara terpisah.
',
'kode_kategori' => '060007',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
20 => 
array (
'id' => 42,
'nama_barang' => 'Saklar Gantung',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Jenis saklar fleksibel yang tidak perlu ditanam di dinding. Biasanya digunakan untuk lampu tidur, lampu gantung, atau kipas angin yang kabelnya perlu ditarik.
',
'kode_kategori' => '060007',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
21 => 
array (
'id' => 43,
'nama_barang' => 'Miniature Circuit Breaker',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Kapasitas 1 phase, 16 Ampere. Cocok untuk pembagian sirkuit listrik rumah tangga agar tidak sering turun.
',
'kode_kategori' => '060008',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
22 => 
array (
'id' => 44,
'nama_barang' => 'Motor Circuit Breaker',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Range arus 0.4-0.63A (GV2ME04) atau lebih tinggi. Memiliki tombol putar/rocker untuk on/off.
',
'kode_kategori' => '060008',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
23 => 
array (
'id' => 45,
'nama_barang' => 'Molded Case Circuit Breaker',
'kode_barang' => NULL,
'barcode' => NULL,
'satuan' => 'biji',
'deskripsi' => 'Berfungsi memutus arus pada panel distribusi utama. Lebih kokoh dibanding MCB.
',
'kode_kategori' => '060008',
'rating' => NULL,
'created_at' => '2026-05-02 07:55:38',
'updated_at' => '2026-05-02 07:55:38',
),
));
        
        
    }
}