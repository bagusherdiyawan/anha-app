import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/loyalty_model.dart';
import '../../widgets/status_badge.dart';

class RedemptionDetailScreen extends StatelessWidget {
  final RedemptionModel redemption;

  const RedemptionDetailScreen({super.key, required this.redemption});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.text),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Detail Penukaran',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Status Badge
            Center(
              child: StatusBadge(
                text: redemption.statusDisplay,
                type: redemption.isClaimed ? BadgeType.green : BadgeType.yellow,
                fontSize: 12,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              ),
            ),
            const SizedBox(height: 16),

            // Reward Info Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: [
                  Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      color: const Color(0xFF374151),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Center(
                      child: Text('💡', style: TextStyle(fontSize: 28)),
                    ),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    redemption.reward?.name ?? 'Hadiah Poin ANHA',
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: AppColors.text,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    redemption.reward?.description ?? 'Hadiah Resmi Toko ANHA',
                    style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                  ),
                  const Divider(height: 24, color: AppColors.border),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _buildInfoColumn('Poin Dipotong', '-${redemption.pointsSpent} Poin', isRed: true),
                      _buildInfoColumn('Tanggal Tukar', redemption.createdAt ?? '-'),
                      _buildInfoColumn('Nilai Hadiah', 'Rp ${CurrencyFormatter.formatNumber(redemption.pointsSpent * 500)}', isGreen: true),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Claim Code Box with dashed border
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.primarySoft,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: AppColors.primary,
                  width: 1.5,
                  style: BorderStyle.solid,
                ),
              ),
              child: Column(
                children: [
                  const Text(
                    'KODE KLAIM HADIAH',
                    style: TextStyle(
                      fontSize: 9,
                      fontWeight: FontWeight.w800,
                      color: AppColors.primary,
                      letterSpacing: 1,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    redemption.claimCode,
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w900,
                      color: AppColors.text,
                      letterSpacing: 4,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Tunjukkan kode atau scan QR ini kepada petugas toko',
                    style: TextStyle(fontSize: 10, color: AppColors.textMuted),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // QR CODE (Wireframe item 23)
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: [
                  QrImageView(
                    data: redemption.claimCode,
                    version: QrVersions.auto,
                    size: 160.0,
                    dataModuleStyle: const QrDataModuleStyle(
                      dataModuleShape: QrDataModuleShape.square,
                      color: AppColors.text,
                    ),
                    eyeStyle: const QrEyeStyle(
                      eyeShape: QrEyeShape.square,
                      color: AppColors.text,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'QR Code Resmi Klaim Hadiah ANHA',
                    style: TextStyle(fontSize: 10, color: AppColors.textLight),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Cara Mengambil Hadiah Instructions
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.primarySoft,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '📋 Cara Mengambil Hadiah di Toko:',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: AppColors.primary,
                    ),
                  ),
                  SizedBox(height: 8),
                  Text('1. Kunjungi toko offline ANHA Electrical terdekat.', style: TextStyle(fontSize: 10, color: AppColors.text, height: 1.4)),
                  Text('2. Tunjukkan kode klaim atau layar QR ini ke petugas kasir.', style: TextStyle(fontSize: 10, color: AppColors.text, height: 1.4)),
                  Text('3. Petugas kasir akan memverifikasi dan menyerahkan hadiah fisik.', style: TextStyle(fontSize: 10, color: AppColors.text, height: 1.4)),
                  Text('4. Status akan otomatis berubah menjadi "Sudah Diambil".', style: TextStyle(fontSize: 10, color: AppColors.text, height: 1.4)),
                ],
              ),
            ),
            const SizedBox(height: 12),

            // Store Location Address Card
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.border),
              ),
              child: const Row(
                children: [
                  Text('📍', style: TextStyle(fontSize: 20)),
                  SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Toko ANHA Electrical',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.text),
                        ),
                        Text(
                          'Jl. Listrik No. 1, Gading Serpong, Tangerang Selatan',
                          style: TextStyle(fontSize: 10, color: AppColors.textMuted),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoColumn(String label, String value, {bool isRed = false, bool isGreen = false}) {
    return Column(
      children: [
        Text(label, style: const TextStyle(fontSize: 9, color: AppColors.textLight)),
        const SizedBox(height: 2),
        Text(
          value,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w800,
            color: isRed
                ? AppColors.danger
                : isGreen
                    ? AppColors.success
                    : AppColors.text,
          ),
        ),
      ],
    );
  }
}
