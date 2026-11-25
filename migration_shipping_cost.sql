-- =====================================================================
-- Migrasi Manual: Penambahan Kolom shipping_cost ke Tabel orders
-- Target Database: Database ANHA Production (MySQL / MariaDB)
-- Tanggal: 14 November 2026
-- Sistem: Backend API api.anhagroup.com
-- =====================================================================

-- 1. Jalankan ALTER TABLE jika kolom shipping_cost belum ada
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `shipping_cost` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 
AFTER `discount_amount`;

-- Catatan untuk MariaDB / MySQL lama yang belum support 'ADD COLUMN IF NOT EXISTS':
-- Jika query di atas syntax error karena versi MySQL, gunakan query standar ini:
-- ALTER TABLE `orders` ADD COLUMN `shipping_cost` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 AFTER `discount_amount`;

-- 2. Verifikasi struktur tabel orders
-- DESCRIBE `orders`;

