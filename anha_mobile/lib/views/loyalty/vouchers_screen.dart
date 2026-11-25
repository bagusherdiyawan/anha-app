import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../providers/cart_provider.dart';
import '../../providers/loyalty_provider.dart';
import '../cart/cart_screen.dart';

class VouchersScreen extends StatefulWidget {
  const VouchersScreen({super.key});

  @override
  State<VouchersScreen> createState() => _VouchersScreenState();
}

class _VouchersScreenState extends State<VouchersScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LoyaltyProvider>().fetchVouchers();
    });
  }

  void _copyVoucherCode(String code) {
    Clipboard.setData(ClipboardData(text: code));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Kode voucher $code berhasil disalin!'),
        backgroundColor: AppColors.success,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final loyalty = context.watch<LoyaltyProvider>();
    final vouchers = loyalty.vouchers;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.text),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Row(
          children: [
            Text('🎟️', style: TextStyle(fontSize: 16)),
            SizedBox(width: 8),
            Text(
              'Voucher Saya',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: AppColors.text,
              ),
            ),
          ],
        ),
      ),
      body: vouchers.isEmpty
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text('🎟️', style: TextStyle(fontSize: 48)),
                  SizedBox(height: 12),
                  Text(
                    'Belum Ada Voucher Tersedia',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: AppColors.text,
                    ),
                  ),
                  SizedBox(height: 6),
                  Text(
                    'Tukarkan poin listrik Anda di Katalog Reward untuk mendapatkan voucher!',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                  ),
                ],
              ),
            )
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: vouchers.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final v = vouchers[index];
                final isPercent = v.discountType == 'percent';
                final discountText = isPercent
                    ? 'Diskon ${v.discountValue.toInt()}%'
                    : 'Diskon ${CurrencyFormatter.format(v.discountValue.toInt())}';

                return Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Column(
                    children: [
                      Padding(
                        padding: const EdgeInsets.all(14),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              width: 44,
                              height: 44,
                              decoration: BoxDecoration(
                                color: AppColors.yellowSoft,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Center(
                                child: Text('⚡', style: TextStyle(fontSize: 22)),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    discountText,
                                    style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w900,
                                      color: AppColors.text,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    'Min. belanja ${CurrencyFormatter.format(v.minPurchase.toInt())}',
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: AppColors.textMuted,
                                    ),
                                  ),
                                  if (v.expiresAt != null) ...[
                                    const SizedBox(height: 4),
                                    Text(
                                      'Berlaku hingga: ${v.expiresAt}',
                                      style: const TextStyle(
                                        fontSize: 10,
                                        color: AppColors.textLight,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        decoration: const BoxDecoration(
                          color: Color(0xFFF9FAFB),
                          borderRadius: BorderRadius.vertical(bottom: Radius.circular(12)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                Text(
                                  v.code,
                                  style: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w800,
                                    color: AppColors.primary,
                                    letterSpacing: 0.8,
                                  ),
                                ),
                                IconButton(
                                  icon: const Icon(Icons.copy, size: 16, color: AppColors.primary),
                                  onPressed: () => _copyVoucherCode(v.code),
                                ),
                              ],
                            ),
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.yellow,
                                elevation: 0,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              ),
                              onPressed: () async {
                                final messenger = ScaffoldMessenger.of(context);
                                final nav = Navigator.of(context);
                                final error = await context.read<CartProvider>().applyPromo(
                                      v.code,
                                      explicitDiscount: isPercent ? null : v.discountValue.toInt(),
                                    );
                                if (error != null) {
                                  messenger.showSnackBar(
                                    SnackBar(
                                      content: Text(error),
                                      backgroundColor: AppColors.danger,
                                    ),
                                  );
                                } else {
                                  messenger.showSnackBar(
                                    SnackBar(
                                      content: Text('Voucher ${v.code} diterapkan ke keranjang!'),
                                      backgroundColor: AppColors.success,
                                    ),
                                  );
                                  nav.push(
                                    MaterialPageRoute(builder: (_) => const CartScreen()),
                                  );
                                }
                              },
                              child: const Text(
                                'Pakai',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.text,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
    );
  }
}
