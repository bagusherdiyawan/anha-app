import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../providers/loyalty_provider.dart';
import 'redemption_history_screen.dart';
import 'reward_catalog_screen.dart';
import 'vouchers_screen.dart';
import 'widgets/reward_card.dart';

class LoyaltyScreen extends StatefulWidget {
  const LoyaltyScreen({super.key});

  @override
  State<LoyaltyScreen> createState() => _LoyaltyScreenState();
}

class _LoyaltyScreenState extends State<LoyaltyScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LoyaltyProvider>().fetchAllLoyaltyData();
    });
  }

  @override
  Widget build(BuildContext context) {
    final loyalty = context.watch<LoyaltyProvider>();
    final balance = loyalty.currentBalance;
    final summary = loyalty.summary;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        automaticallyImplyLeading: false,
        title: const Row(
          children: [
            Text('⚡', style: TextStyle(fontSize: 16)),
            SizedBox(width: 8),
            Text(
              'Poin & Reward Saya',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: AppColors.primary,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.confirmation_number_outlined, color: AppColors.text),
            tooltip: 'Voucher Saya',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const VouchersScreen()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.history, color: AppColors.text),
            tooltip: 'Riwayat Penukaran Hadiah',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const RedemptionHistoryScreen()),
              );
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => context.read<LoyaltyProvider>().fetchAllLoyaltyData(),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (loyalty.loadError != null) ...[
              Container(
                width: double.infinity,
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.yellowSoft,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppColors.yellowDark),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.warning_amber_rounded, size: 16, color: AppColors.yellowDark),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        loyalty.loadError!,
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.text),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            // TOTAL POIN GOLD CARD (Wireframe item 20)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.yellow,
                borderRadius: BorderRadius.circular(14),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.yellow.withOpacity(0.3),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Stack(
                children: [
                  Positioned(
                    right: -10,
                    bottom: -15,
                    child: Text(
                      '⚡',
                      style: TextStyle(
                        fontSize: 80,
                        color: Colors.black.withOpacity(0.08),
                      ),
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Text('⚡', style: TextStyle(fontSize: 14)),
                          SizedBox(width: 4),
                          Text(
                            'TOTAL SALDO POIN LISTRIK',
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w900,
                              color: AppColors.yellowDark,
                              letterSpacing: 0.8,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        '${CurrencyFormatter.formatNumber(balance)} Poin',
                        style: const TextStyle(
                          fontSize: 26,
                          fontWeight: FontWeight.w900,
                          color: AppColors.text,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Setara dengan ${CurrencyFormatter.format(balance * 500)} (Rp 500/poin)',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: AppColors.yellowDark,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // STATS ROW (MASUK / KELUAR / HANGUS)
            Container(
              padding: const EdgeInsets.symmetric(vertical: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  _buildStatItem('MASUK', '+${summary?.totalEarned ?? 0}', AppColors.success),
                  Container(width: 1, height: 30, color: AppColors.border),
                  _buildStatItem('KELUAR', '-${summary?.totalSpent ?? 0}', AppColors.danger),
                  Container(width: 1, height: 30, color: AppColors.border),
                  _buildStatItem('HANGUS', '${summary?.expired ?? 0}', AppColors.textLight),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // KATALOG REWARD SECTION
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Katalog Reward',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                    color: AppColors.text,
                  ),
                ),
                InkWell(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const RewardCatalogScreen()),
                    );
                  },
                  child: const Text(
                    'Lihat Semua',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: AppColors.primary,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),

            // Preview Reward Cards (from server catalog)
            if (loyalty.isLoading && loyalty.rewards.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 20),
                child: Center(child: CircularProgressIndicator()),
              )
            else if (loyalty.rewards.isEmpty)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.border),
                ),
                child: const Text(
                  'Belum ada reward tersedia saat ini.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 12, color: AppColors.textMuted),
                ),
              )
            else
              Builder(
                builder: (context) {
                  final previewRewards = loyalty.rewards.take(2).toList();
                  return Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: RewardCard(
                          reward: previewRewards[0],
                          currentBalance: balance,
                          onRedeem: () => showRewardRedemptionSheet(
                            context,
                            previewRewards[0],
                            balance,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: previewRewards.length > 1
                            ? RewardCard(
                                reward: previewRewards[1],
                                currentBalance: balance,
                                onRedeem: () => showRewardRedemptionSheet(
                                  context,
                                  previewRewards[1],
                                  balance,
                                ),
                              )
                            : const SizedBox.shrink(),
                      ),
                    ],
                  );
                },
              ),
            const SizedBox(height: 20),

            // RIWAYAT POIN SECTION
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Riwayat Perolehan Poin',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                    color: AppColors.text,
                  ),
                ),
                InkWell(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const RedemptionHistoryScreen()),
                    );
                  },
                  child: const Text(
                    'Klaim Hadiah ›',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: AppColors.primary,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),

            // Transactions list
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.border),
              ),
              child: ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: loyalty.transactions.length,
                separatorBuilder: (_, __) => const Divider(height: 1, color: AppColors.divider),
                itemBuilder: (context, index) {
                  final tx = loyalty.transactions[index];
                  final isPositive = tx.amount > 0;
                  return Padding(
                    padding: const EdgeInsets.all(12),
                    child: Row(
                      children: [
                        Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: isPositive ? AppColors.successSoft : AppColors.yellowSoft,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Center(
                            child: Text(
                              isPositive ? '🛍️' : '🎁',
                              style: const TextStyle(fontSize: 18),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                tx.description,
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.text,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                tx.createdAt ?? '-',
                                style: const TextStyle(fontSize: 9, color: AppColors.textLight),
                              ),
                            ],
                          ),
                        ),
                        Text(
                          isPositive ? '+${tx.amount}' : '${tx.amount}',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w900,
                            color: isPositive ? AppColors.success : AppColors.danger,
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
        ),
      ),
    );
  }

  Widget _buildStatItem(String label, String value, Color color) {
    return Expanded(
      child: Column(
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 8,
              fontWeight: FontWeight.w800,
              color: AppColors.textLight,
              letterSpacing: 0.5,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w900,
              color: color,
            ),
          ),
        ],
      ),
    );
  }
}
