import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../models/loyalty_model.dart';
import '../../providers/loyalty_provider.dart';
import '../../widgets/status_badge.dart';
import 'redemption_detail_screen.dart';

class RedemptionHistoryScreen extends StatefulWidget {
  const RedemptionHistoryScreen({super.key});

  @override
  State<RedemptionHistoryScreen> createState() => _RedemptionHistoryScreenState();
}

class _RedemptionHistoryScreenState extends State<RedemptionHistoryScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LoyaltyProvider>().fetchRedemptions();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final loyalty = context.watch<LoyaltyProvider>();
    final redemptions = loyalty.redemptions;

    final pendingList = redemptions.where((r) => !r.isClaimed).toList();
    final claimedList = redemptions.where((r) => r.isClaimed).toList();

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
          'Riwayat Penukaran Poin',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textLight,
          indicatorColor: AppColors.primary,
          labelStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
          tabs: const [
            Tab(text: 'Semua'),
            Tab(text: 'Belum Diambil'),
            Tab(text: 'Sudah Diambil'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildList(redemptions),
          _buildList(pendingList),
          _buildList(claimedList),
        ],
      ),
    );
  }

  Widget _buildList(List<RedemptionModel> list) {
    if (list.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text('🎁', style: TextStyle(fontSize: 36)),
            SizedBox(height: 10),
            Text(
              'Belum ada penukaran hadiah',
              style: TextStyle(fontSize: 13, color: AppColors.textMuted),
            ),
          ],
        ),
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: list.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final item = list[index];
        return InkWell(
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => RedemptionDetailScreen(redemption: item),
              ),
            );
          },
          borderRadius: BorderRadius.circular(10),
          child: Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppColors.border),
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: const Color(0xFF374151),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Center(
                        child: Text('💡', style: TextStyle(fontSize: 22)),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.reward?.name ?? 'Hadiah ANHA Electrical',
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              color: AppColors.text,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '${item.createdAt ?? '-'} · ${item.pointsSpent} Poin',
                            style: const TextStyle(fontSize: 10, color: AppColors.textLight),
                          ),
                        ],
                      ),
                    ),
                    StatusBadge(
                      text: item.statusDisplay,
                      type: item.isClaimed ? BadgeType.green : BadgeType.yellow,
                    ),
                  ],
                ),
                const Divider(height: 16, color: AppColors.divider),
                const Align(
                  alignment: Alignment.centerRight,
                  child: Text(
                    'Lihat Kode Klaim & QR ›',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: AppColors.primary,
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
