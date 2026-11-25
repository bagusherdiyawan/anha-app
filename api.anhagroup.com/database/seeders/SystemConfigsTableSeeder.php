<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SystemConfigsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('system_configs')->delete();
        
        \DB::table('system_configs')->insert(array (
            0 => 
            array (
                'id' => 1,
                'key' => 'whatsapp_number',
                'value' => '6281234567890',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Nomor WhatsApp kontak',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            1 => 
            array (
                'id' => 2,
                'key' => 'phone_number',
                'value' => '021-1234567',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Nomor telepon kontak',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            2 => 
            array (
                'id' => 3,
                'key' => 'email_contact',
                'value' => 'info@anha-elektronik.com',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Email kontak',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            3 => 
            array (
                'id' => 4,
                'key' => 'whatsapp_template',
                'value' => 'Halo ANHA Elektronik, saya ingin bertanya tentang...',
                'type' => 'string',
                'group' => 'contact',
                'description' => 'Template pesan WhatsApp',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            4 => 
            array (
                'id' => 5,
                'key' => 'points_rate_per_point',
                'value' => '40000',
                'type' => 'integer',
                'group' => 'loyalty',
            'description' => 'Nilai belanja per 1 poin (Rp)',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            5 => 
            array (
                'id' => 6,
                'key' => 'points_value_in_rupiah',
                'value' => '500',
                'type' => 'integer',
                'group' => 'loyalty',
                'description' => 'Nilai 1 poin dalam Rupiah',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            6 => 
            array (
                'id' => 7,
                'key' => 'points_expiry_days',
                'value' => '365',
                'type' => 'integer',
                'group' => 'loyalty',
            'description' => 'Masa berlaku poin (hari)',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            7 => 
            array (
                'id' => 8,
                'key' => 'shipping_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'shipping',
                'description' => 'Aktifkan biaya pengiriman',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            8 => 
            array (
                'id' => 9,
                'key' => 'shipping_flat_rate',
                'value' => '0',
                'type' => 'integer',
                'group' => 'shipping',
            'description' => 'Biaya pengiriman flat (Rp)',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            9 => 
            array (
                'id' => 10,
                'key' => 'order_cancel_minutes',
                'value' => '60',
                'type' => 'integer',
                'group' => 'general',
            'description' => 'Batas waktu pembatalan order (menit)',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
            10 => 
            array (
                'id' => 11,
                'key' => 'low_stock_threshold',
                'value' => '10',
                'type' => 'integer',
                'group' => 'general',
                'description' => 'Batas stok rendah default',
                'created_at' => '2026-05-11 23:53:33',
                'updated_at' => '2026-05-11 23:53:33',
            ),
        ));
        
        
    }
}