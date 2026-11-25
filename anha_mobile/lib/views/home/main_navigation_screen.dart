import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/loyalty_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/order_provider.dart';
import '../browse/categories_screen.dart';
import '../loyalty/loyalty_screen.dart';
import '../orders/order_history_screen.dart';
import '../profile/profile_screen.dart';
import 'home_screen.dart';

class MainNavigationScreen extends StatefulWidget {
  final int initialIndex;

  const MainNavigationScreen({super.key, this.initialIndex = 0});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen>
    with WidgetsBindingObserver {
  late int _currentIndex;

  final List<Widget> _screens = const [
    HomeScreen(),
    CategoriesScreen(),
    OrderHistoryScreen(),
    LoyaltyScreen(),
    ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _refreshTabData(_currentIndex);
    }
  }

  void _refreshTabData(int index) {
    if (!mounted) return;
    switch (index) {
      case 0:
        context.read<NotificationProvider>().fetchUnreadCount();
        break;
      case 2:
        context.read<OrderProvider>().fetchOrders();
        break;
      case 3:
        context.read<LoyaltyProvider>().fetchAllLoyaltyData(silent: true);
        break;
      case 4:
        context.read<AuthProvider>().fetchProfile();
        context.read<OrderProvider>().fetchOrders();
        context.read<LoyaltyProvider>().fetchSummary();
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.border, width: 1)),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 4),
            child: Row(
              children: [
                _buildNavItem(0, '🏠', 'HOME'),
                _buildNavItem(1, '🗂', 'CATEGORIES'),
                _buildNavItem(2, '📋', 'ORDERS'),
                _buildNavItem(3, '⚡', 'LOYALTY'),
                _buildNavItem(4, '👤', 'PROFILE'),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(int index, String icon, String label) {
    final active = _currentIndex == index;
    return Expanded(
      child: InkWell(
        onTap: () {
          setState(() => _currentIndex = index);
          _refreshTabData(index);
        },
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 24,
                height: 24,
                decoration: BoxDecoration(
                  color: active
                      ? (index == 3 ? AppColors.yellow : AppColors.primary)
                      : const Color(0xFFD1D5DB),
                  borderRadius: BorderRadius.circular(5),
                ),
                child: Center(
                  child: Text(
                    icon,
                    style: TextStyle(
                      fontSize: 12,
                      color: active ? Colors.white : AppColors.text,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 3),
              Text(
                label,
                style: TextStyle(
                  fontSize: 8,
                  fontWeight: FontWeight.w700,
                  color: active ? AppColors.primary : AppColors.textLight,
                  letterSpacing: 0.3,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
