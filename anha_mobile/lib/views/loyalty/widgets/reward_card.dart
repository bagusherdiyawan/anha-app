import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../models/loyalty_model.dart';
import '../../../providers/loyalty_provider.dart';
import '../../../widgets/custom_button.dart';
import '../redemption_detail_screen.dart';

Color rewardCardBackground(RewardModel reward) {
  switch (reward.normalizedCategory) {
    case 'voucher':
      return AppColors.primary;
    case 'discount':
      return const Color(0xFF059669);
    default:
      return const Color(0xFF374151);
  }
}

String rewardCardEmoji(RewardModel reward) {
  switch (reward.normalizedCategory) {
    case 'voucher':
      return '🎫';
    case 'discount':
      return '💰';
    default:
      return '🎁';
  }
}

class RewardCard extends StatelessWidget {
  final RewardModel reward;
  final int currentBalance;
  final VoidCallback? onRedeem;

  const RewardCard({
    super.key,
    required this.reward,
    required this.currentBalance,
    this.onRedeem,
  });

  @override
  Widget build(BuildContext context) {
    final canAfford = currentBalance >= reward.pointsRequired;
    final inStock = reward.inStock;
    final emoji = rewardCardEmoji(reward);

    return Opacity(
      opacity: canAfford && inStock ? 1.0 : 0.65,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.border),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: 68,
              width: double.infinity,
              color: rewardCardBackground(reward),
              child: Stack(
                fit: StackFit.expand,
                children: [
                  if (reward.image != null)
                    Image.network(
                      reward.image!,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Center(
                        child: Text(emoji, style: const TextStyle(fontSize: 28)),
                      ),
                    )
                  else
                    Center(
                      child: Text(emoji, style: const TextStyle(fontSize: 28)),
                    ),
                  Positioned(
                    top: 6,
                    left: 6,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.yellow,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        '${CurrencyFormatter.formatNumber(reward.pointsRequired)} Poin',
                        style: const TextStyle(
                          fontSize: 8,
                          fontWeight: FontWeight.w800,
                          color: AppColors.text,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    height: 28,
                    child: Align(
                      alignment: Alignment.centerLeft,
                      child: Text(
                        reward.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: AppColors.text,
                          height: 1.2,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),
                  if (!inStock)
                    const Text(
                      'STOK HABIS',
                      style: TextStyle(
                        fontSize: 8,
                        fontWeight: FontWeight.w800,
                        color: AppColors.danger,
                      ),
                    )
                  else if (!canAfford)
                    const Text(
                      'Poin tidak cukup',
                      style: TextStyle(
                        fontSize: 8,
                        fontWeight: FontWeight.w700,
                        color: AppColors.danger,
                      ),
                    )
                  else
                    Text(
                      reward.isUnlimitedStock
                          ? 'Tersedia'
                          : 'Tersedia: ${reward.stock} pcs',
                      style: const TextStyle(
                        fontSize: 8,
                        color: AppColors.textLight,
                      ),
                    ),
                  const SizedBox(height: 6),
                  CustomButton(
                    text: 'Tukar',
                    height: 30,
                    variant: canAfford && inStock
                        ? ButtonVariant.yellow
                        : ButtonVariant.outline,
                    onPressed: canAfford && inStock ? onRedeem : null,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

void showRewardRedemptionSheet(
  BuildContext context,
  RewardModel reward,
  int currentBalance,
) {
  final sisaPoin = currentBalance - reward.pointsRequired;
  final isPhysical = reward.normalizedCategory == 'physical';
  final emoji = rewardCardEmoji(reward);

  showModalBottomSheet(
    context: context,
    backgroundColor: Colors.transparent,
    builder: (ctx) {
      return Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 16),
            Container(
              width: 52,
              height: 52,
              decoration: BoxDecoration(
                color: rewardCardBackground(reward),
                borderRadius: BorderRadius.circular(12),
              ),
              clipBehavior: Clip.antiAlias,
              child: reward.image != null
                  ? Image.network(
                      reward.image!,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Center(
                        child: Text(emoji, style: const TextStyle(fontSize: 26)),
                      ),
                    )
                  : Center(
                      child: Text(emoji, style: const TextStyle(fontSize: 26)),
                    ),
            ),
            const SizedBox(height: 10),
            Text(
              reward.name,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: AppColors.text,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              isPhysical
                  ? 'Ambil langsung di toko offline ANHA Electrical'
                  : 'Voucher/potongan akan otomatis masuk ke akun Anda',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.primarySoft,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: Column(
                children: [
                  _buildModalRow(
                    'Poin dibutuhkan',
                    '${reward.pointsRequired} Poin',
                    color: AppColors.primary,
                  ),
                  const SizedBox(height: 6),
                  _buildModalRow('Saldo Anda saat ini', '$currentBalance Poin'),
                  const Divider(height: 16, color: Color(0xFFBFDBFE)),
                  _buildModalRow(
                    'Sisa poin Anda',
                    '$sisaPoin Poin',
                    color: AppColors.success,
                    isBold: true,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            Text(
              isPhysical
                  ? 'Penukaran tidak dapat dibatalkan setelah dikonfirmasi. Hadiah diambil langsung ke toko.'
                  : 'Penukaran tidak dapat dibatalkan setelah dikonfirmasi.',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 10,
                color: AppColors.textLight,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 18),
            CustomButton(
              text: '⚡ Tukar Sekarang',
              variant: ButtonVariant.yellow,
              onPressed: () async {
                final nav = Navigator.of(context);
                final messenger = ScaffoldMessenger.of(context);
                final loyaltyProvider = context.read<LoyaltyProvider>();
                Navigator.pop(ctx);
                final redemption = await loyaltyProvider.redeemReward(reward);
                if (redemption != null) {
                  nav.push(
                    MaterialPageRoute(
                      builder: (_) => RedemptionDetailScreen(redemption: redemption),
                    ),
                  );
                } else {
                  messenger.showSnackBar(
                    SnackBar(
                      content: Text(
                        loyaltyProvider.errorMessage ?? 'Gagal menukarkan reward.',
                      ),
                      backgroundColor: AppColors.danger,
                    ),
                  );
                }
              },
            ),
            const SizedBox(height: 8),
            CustomButton(
              text: 'Batal',
              variant: ButtonVariant.outline,
              onPressed: () => Navigator.pop(ctx),
            ),
          ],
        ),
      );
    },
  );
}

Widget _buildModalRow(
  String label,
  String value, {
  Color? color,
  bool isBold = false,
}) {
  return Row(
    mainAxisAlignment: MainAxisAlignment.spaceBetween,
    children: [
      Text(
        label,
        style: TextStyle(
          fontSize: 11,
          fontWeight: isBold ? FontWeight.w700 : FontWeight.w500,
          color: AppColors.textMuted,
        ),
      ),
      Text(
        value,
        style: TextStyle(
          fontSize: 12,
          fontWeight: isBold ? FontWeight.w900 : FontWeight.w700,
          color: color ?? AppColors.text,
        ),
      ),
    ],
  );
}

