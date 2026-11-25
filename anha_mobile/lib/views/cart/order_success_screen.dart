import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../widgets/custom_button.dart';
import '../home/main_navigation_screen.dart';
import '../orders/order_tracking_screen.dart';

class OrderSuccessScreen extends StatelessWidget {
  final int? orderId;
  final String invoiceNumber;
  final int earnedPoints;
  final int total;

  const OrderSuccessScreen({
    super.key,
    required this.invoiceNumber,
    this.orderId,
    this.earnedPoints = 0,
    this.total = 0,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFF),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Spacer(),
              // Big Green Checkmark
              Container(
                width: 76,
                height: 76,
                decoration: const BoxDecoration(
                  color: AppColors.successSoft,
                  shape: BoxShape.circle,
                ),
                child: const Center(
                  child: Text('✅', style: TextStyle(fontSize: 36)),
                ),
              ),
              const SizedBox(height: 18),
              const Text(
                'Pesanan Berhasil!',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                  color: AppColors.text,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Pesanan Anda telah diterima dan sedang diproses oleh tim kami. Pembayaran tunai saat barang tiba (COD).',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 12,
                  color: AppColors.textMuted,
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 24),

              // Invoice & ETA Info Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.03),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    const Text(
                      'NOMOR PESANAN',
                      style: TextStyle(
                        fontSize: 9,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textLight,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      invoiceNumber,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                        color: AppColors.primary,
                      ),
                    ),
                    const Divider(height: 20, color: AppColors.border),
                    const Text(
                      'STATUS PESANAN',
                      style: TextStyle(
                        fontSize: 9,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textLight,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Pesanan Diterima & Sedang Disiapkan',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: AppColors.text,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),

              // Points Info
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.primarySoft,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: Row(
                  children: [
                    const Text('⚡', style: TextStyle(fontSize: 18)),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '+$earnedPoints Poin Listrik',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              color: AppColors.primary,
                            ),
                          ),
                          const Text(
                            'Akan dikreditkan otomatis setelah pesanan selesai diterima.',
                            style: TextStyle(
                              fontSize: 10,
                              color: AppColors.textMuted,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const Spacer(),

              // Action Buttons
              CustomButton(
                text: 'Lacak Pesanan (Live Map OSM)',
                variant: ButtonVariant.primary,
                icon: const Text('🚚', style: TextStyle(fontSize: 16)),
                onPressed: () {
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(
                      builder: (_) => OrderTrackingScreen(
                        orderId: orderId ?? 0,
                        invoiceNumber: invoiceNumber,
                      ),
                    ),
                  );
                },
              ),
              const SizedBox(height: 10),
              CustomButton(
                text: 'Kembali ke Beranda',
                variant: ButtonVariant.outline,
                onPressed: () {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
                    (route) => false,
                  );
                },
              ),
              const SizedBox(height: 10),
            ],
          ),
        ),
      ),
    );
  }
}
