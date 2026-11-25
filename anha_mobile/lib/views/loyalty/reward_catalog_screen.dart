import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/loyalty_model.dart';
import '../../providers/loyalty_provider.dart';
import 'widgets/reward_card.dart';

class RewardCatalogScreen extends StatefulWidget {
  const RewardCatalogScreen({super.key});

  @override
  State<RewardCatalogScreen> createState() => _RewardCatalogScreenState();
}

class _RewardCatalogScreenState extends State<RewardCatalogScreen> {
  String _selectedFilter = 'Semua';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LoyaltyProvider>().fetchRewards();
    });
  }

  @override
  Widget build(BuildContext context) {
    final loyalty = context.watch<LoyaltyProvider>();
    final balance = loyalty.currentBalance;

    List<RewardModel> filteredList = List.from(loyalty.rewards);
    if (_selectedFilter == 'Voucher') {
      filteredList = filteredList.where((r) => r.normalizedCategory == 'voucher').toList();
    } else if (_selectedFilter == 'Hadiah Barang') {
      filteredList = filteredList.where((r) => r.normalizedCategory == 'physical').toList();
    } else if (_selectedFilter == 'Potongan') {
      filteredList = filteredList.where((r) => r.normalizedCategory == 'discount').toList();
    }

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
          'Katalog Tukar Poin',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
      ),
      body: Column(
        children: [
          // Yellow Top Balance Banner
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            color: AppColors.yellow,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'SALDO POIN ANDA',
                      style: TextStyle(
                        fontSize: 9,
                        fontWeight: FontWeight.w800,
                        color: AppColors.yellowDark,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${CurrencyFormatter.formatNumber(balance)} Poin',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w900,
                        color: AppColors.text,
                      ),
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    const Text(
                      'Setara dengan',
                      style: TextStyle(fontSize: 9, color: AppColors.yellowDark),
                    ),
                    Text(
                      CurrencyFormatter.format(balance * 500),
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: AppColors.text,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Category Filter Chips
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            color: Colors.white,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterChip('Semua'),
                  const SizedBox(width: 8),
                  _buildFilterChip('Voucher'),
                  const SizedBox(width: 8),
                  _buildFilterChip('Hadiah Barang'),
                  const SizedBox(width: 8),
                  _buildFilterChip('Potongan'),
                ],
              ),
            ),
          ),
          const Divider(height: 1, color: AppColors.border),

          // Rewards Grid
          Expanded(
            child: loyalty.isLoading && filteredList.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: () => context.read<LoyaltyProvider>().fetchRewards(),
                    child: filteredList.isEmpty
                        ? ListView(
                            physics: const AlwaysScrollableScrollPhysics(),
                            children: [
                              SizedBox(height: MediaQuery.of(context).size.height * 0.25),
                              Center(
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    const Text('🎁', style: TextStyle(fontSize: 40)),
                                    const SizedBox(height: 10),
                                    Text(
                                      'Belum ada reward untuk kategori $_selectedFilter',
                                      style: const TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w700,
                                        color: AppColors.textMuted,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          )
                        : GridView.builder(
                            padding: const EdgeInsets.all(16),
                            physics: const AlwaysScrollableScrollPhysics(),
                            itemCount: filteredList.length,
                            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              mainAxisSpacing: 12,
                              crossAxisSpacing: 12,
                              childAspectRatio: 0.72,
                            ),
                            itemBuilder: (context, index) {
                              final reward = filteredList[index];
                              return RewardCard(
                                reward: reward,
                                currentBalance: balance,
                                onRedeem: () => showRewardRedemptionSheet(
                                  context,
                                  reward,
                                  balance,
                                ),
                              );
                            },
                          ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label) {
    final active = _selectedFilter == label;
    return InkWell(
      onTap: () => setState(() => _selectedFilter = label),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: active ? AppColors.primary : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: active ? AppColors.primary : AppColors.border,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: active ? FontWeight.w700 : FontWeight.w600,
            color: active ? Colors.white : AppColors.text,
          ),
        ),
      ),
    );
  }
}
