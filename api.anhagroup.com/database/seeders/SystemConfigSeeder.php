<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // Contact
            ['key' => 'whatsapp_number', 'value' => '6281234567890', 'type' => 'string', 'group' => 'contact', 'description' => 'Nomor WhatsApp kontak'],
            ['key' => 'phone_number', 'value' => '021-1234567', 'type' => 'string', 'group' => 'contact', 'description' => 'Nomor telepon kontak'],
            ['key' => 'email_contact', 'value' => 'info@anha-elektronik.com', 'type' => 'string', 'group' => 'contact', 'description' => 'Email kontak'],
            ['key' => 'whatsapp_template', 'value' => 'Halo ANHA Elektronik, saya ingin bertanya tentang...', 'type' => 'string', 'group' => 'contact', 'description' => 'Template pesan WhatsApp'],
            // Loyalty
            ['key' => 'points_rate_per_point', 'value' => '40000', 'type' => 'integer', 'group' => 'loyalty', 'description' => 'Nilai belanja per 1 poin (Rp)'],
            ['key' => 'points_value_in_rupiah', 'value' => '500', 'type' => 'integer', 'group' => 'loyalty', 'description' => 'Nilai 1 poin dalam Rupiah'],
            ['key' => 'points_expiry_days', 'value' => '365', 'type' => 'integer', 'group' => 'loyalty', 'description' => 'Masa berlaku poin (hari)'],
            // Shipping
            ['key' => 'shipping_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'shipping', 'description' => 'Aktifkan biaya pengiriman'],
            ['key' => 'shipping_flat_rate', 'value' => '0', 'type' => 'integer', 'group' => 'shipping', 'description' => 'Biaya pengiriman flat (Rp)'],
            // General
            ['key' => 'order_cancel_minutes', 'value' => '60', 'type' => 'integer', 'group' => 'general', 'description' => 'Batas waktu pembatalan order (menit)'],
            ['key' => 'low_stock_threshold', 'value' => '10', 'type' => 'integer', 'group' => 'general', 'description' => 'Batas stok rendah default'],
            ['key' => 'minimum_purchase_amount', 'value' => '100000', 'type' => 'integer', 'group' => 'general', 'description' => 'Minimal belanja untuk checkout (Rp)'],
        ];

        foreach ($configs as $config) {
            SystemConfig::updateOrCreate(['key' => $config['key']], $config);
        }
    }
}
