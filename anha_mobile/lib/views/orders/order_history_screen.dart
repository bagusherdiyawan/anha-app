import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/order_model.dart';
import '../../providers/cart_provider.dart';
import '../../providers/order_provider.dart';
import '../../widgets/status_badge.dart';
import '../cart/cart_screen.dart';
import 'order_tracking_screen.dart';

class OrderHistoryScreen extends StatefulWidget {
  const OrderHistoryScreen({super.key});

  @override
  State<OrderHistoryScreen> createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  int? _reorderingOrderId;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<OrderProvider>().fetchOrders();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _handleReorder(OrderModel order) async {
    if (_reorderingOrderId != null) return;
    setState(() => _reorderingOrderId = order.id);

    final cart = context.read<CartProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);

    final result = await cart.reorderOrderItems(order.items);
    if (!mounted) return;
    setState(() => _reorderingOrderId = null);

    if (result.addedCount == 0) {
      messenger.showSnackBar(
        SnackBar(
          content: Text(
            result.errors.isNotEmpty
                ? result.errors.first
                : 'Gagal memasukkan item pesanan ke keranjang.',
          ),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    if (result.hasErrors) {
      messenger.showSnackBar(
        SnackBar(
          content: Text('Sebagian item gagal ditambahkan: ${result.errors.first}'),
          backgroundColor: AppColors.warning,
        ),
      );
    } else {
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Item pesanan dimasukkan kembali ke keranjang!'),
          backgroundColor: AppColors.success,
        ),
      );
    }

    navigator.push(
      MaterialPageRoute(builder: (_) => const CartScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    final orderProvider = context.watch<OrderProvider>();

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
              'Riwayat Pesanan',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: AppColors.primary,
              ),
            ),
          ],
        ),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textLight,
          indicatorColor: AppColors.primary,
          indicatorWeight: 3,
          labelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
          tabs: const [
            Tab(text: 'Semua'),
            Tab(text: 'Aktif'),
            Tab(text: 'Selesai'),
            Tab(text: 'Dibatalkan'),
          ],
        ),
      ),
      body: orderProvider.isLoading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabController,
              children: [
                _buildOrderList(orderProvider.orders),
                _buildOrderList(orderProvider.activeOrders),
                _buildOrderList(orderProvider.completedOrders),
                _buildOrderList(orderProvider.cancelledOrders),
              ],
            ),
    );
  }

  Widget _buildOrderList(List<OrderModel> list) {
    return RefreshIndicator(
      onRefresh: () => context.read<OrderProvider>().fetchOrders(),
      child: list.isEmpty
          ? ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              children: [
                SizedBox(height: MediaQuery.of(context).size.height * 0.25),
                const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text('📋', style: TextStyle(fontSize: 38)),
                      SizedBox(height: 10),
                      Text(
                        'Tidak ada pesanan di kategori ini',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: AppColors.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            )
          : ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final order = list[index];
        BadgeType bType;
        if (order.status.toLowerCase() == 'completed' || order.status.toLowerCase() == 'selesai') {
          bType = BadgeType.green;
        } else if (order.status.toLowerCase() == 'cancelled') {
          bType = BadgeType.red;
        } else {
          bType = BadgeType.blue;
        }

        return InkWell(
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => OrderTrackingScreen(
                  orderId: order.id,
                  invoiceNumber: order.invoice,
                ),
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
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.02),
                  blurRadius: 4,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      order.invoice,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: AppColors.text,
                      ),
                    ),
                    StatusBadge(text: order.statusLabel, type: bType),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  '${order.createdAt != null && order.createdAt!.isNotEmpty ? order.createdAt! : 'Pesanan'} · ${order.items.length} item',
                  style: const TextStyle(fontSize: 10, color: AppColors.textLight),
                ),
                const Divider(height: 18, color: AppColors.divider),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Text(
                          CurrencyFormatter.format(order.total),
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w900,
                            color: AppColors.accent,
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Text(
                          'COD',
                          style: TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.w700,
                            color: AppColors.textLight,
                          ),
                        ),
                      ],
                    ),
                    Row(
                      children: [
                        if (order.status.toLowerCase() == 'completed' || order.status.toLowerCase() == 'selesai') ...[
                          Builder(
                            builder: (_) {
                              final isReordering = _reorderingOrderId == order.id;
                              return InkWell(
                                onTap: _reorderingOrderId == null
                                    ? () => _handleReorder(order)
                                    : null,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                  decoration: BoxDecoration(
                                    color: isReordering ? AppColors.yellowSoft : AppColors.yellow,
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    isReordering ? 'Memproses...' : 'Beli Lagi',
                                    style: const TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w800,
                                      color: AppColors.text,
                                    ),
                                  ),
                                ),
                              );
                            },
                          ),
                          const SizedBox(width: 8),
                        ],
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            border: Border.all(color: AppColors.border),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'Lacak Detail',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: AppColors.text,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    ),
    );
  }
}

