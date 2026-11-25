import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../providers/address_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/feedback_provider.dart';
import '../../providers/loyalty_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/order_provider.dart';
import '../../widgets/status_badge.dart';
import '../admin/admin_add_product_screen.dart';
import '../auth/code_login_screen.dart';
import 'address_list_screen.dart';
import 'edit_profile_screen.dart';
import 'feedback_list_screen.dart';
import 'notification_settings_screen.dart';
import 'notifications_screen.dart';
import 'rekening_screen.dart';
import '../loyalty/vouchers_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final loyalty = context.watch<LoyaltyProvider>();
    final orderProvider = context.watch<OrderProvider>();
    final user = auth.currentUser;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        elevation: 0,
        automaticallyImplyLeading: false,
        title: const Row(
          children: [
            Text('⚡', style: TextStyle(fontSize: 16)),
            SizedBox(width: 8),
            Text(
              'Profil Saya',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: Colors.white,
              ),
            ),
          ],
        ),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Top Blue Header (matching wireframe)
            Container(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 20),
              color: AppColors.primary,
              child: Row(
                children: [
                  Builder(builder: (context) {
                    final photoUrl = user?.photoUrl;
                    final hasPhoto = photoUrl != null && photoUrl.isNotEmpty;
                    final fullUrl = hasPhoto
                        ? (photoUrl.startsWith('http') ? photoUrl : '${ApiEndpoints.storageUrl}$photoUrl')
                        : null;

                    return Container(
                      width: 54,
                      height: 54,
                      decoration: BoxDecoration(
                        color: AppColors.yellow,
                        shape: BoxShape.circle,
                        image: fullUrl != null
                            ? DecorationImage(
                                image: NetworkImage(fullUrl),
                                fit: BoxFit.cover,
                              )
                            : null,
                      ),
                      child: fullUrl == null
                          ? Center(
                              child: Text(
                                user != null && user.name.isNotEmpty
                                    ? user.name.split(' ').map((e) => e.isNotEmpty ? e[0] : '').take(2).join()
                                    : 'AN',
                                style: const TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.w900,
                                  color: AppColors.text,
                                ),
                              ),
                            )
                          : null,
                    );
                  }),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          user?.name ?? 'Pelanggan ANHA',
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w900,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          user?.phone ?? user?.email ?? '0812-xxxx-xxxx',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.white.withOpacity(0.85),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.yellow,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            user?.isB2B == true
                                ? 'Mitra B2B Grosir'
                                : user?.isAdmin == true
                                    ? 'Admin Official'
                                    : 'B2C Retail',
                            style: const TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w800,
                              color: AppColors.text,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Text('✎', style: TextStyle(fontSize: 20, color: AppColors.yellow)),
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const EditProfileScreen()),
                      );
                    },
                  ),
                ],
              ),
            ),

            // Stats Row: Total Order / Poin / Referral
            Container(
              padding: const EdgeInsets.symmetric(vertical: 14),
              color: Colors.white,
              child: Row(
                children: [
                  _buildStatColumn(
                    (user != null && user.totalOrders > 0
                            ? user.totalOrders
                            : orderProvider.orders.length)
                        .toString(),
                    'Total Order',
                    AppColors.primary,
                  ),
                  Container(width: 1, height: 32, color: AppColors.border),
                  _buildStatColumn(
                    CurrencyFormatter.formatNumber(loyalty.currentBalance),
                    'Poin',
                    AppColors.yellowDark,
                  ),
                  Container(width: 1, height: 32, color: AppColors.border),
                  _buildStatColumn(
                    user?.subscriptionNumber?.isNotEmpty == true
                        ? user!.subscriptionNumber!
                        : (user != null ? 'ANHA-${user.id.toString().padLeft(4, '0')}' : '-'),
                    'Kode Member / Ref',
                    AppColors.accent,
                  ),
                ],
              ),
            ),
            Container(height: 8, color: AppColors.divider),

            // Settings Group 1
            Container(
              color: Colors.white,
              child: Column(
                children: [
                  _buildMenuRow(
                    icon: '👤',
                    title: 'Edit Profil',
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const EditProfileScreen()),
                      );
                    },
                  ),
                  _buildDivider(),
                  _buildMenuRow(
                    icon: '📍',
                    title: 'Alamat Pengiriman (Map OSM)',
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const AddressListScreen()),
                      );
                    },
                  ),
                  _buildDivider(),
                  _buildMenuRow(
                    icon: '🎟️',
                    title: 'Voucher Belanja Saya',
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const VouchersScreen()),
                      );
                    },
                  ),
                  _buildDivider(),
                  _buildMenuRow(
                    icon: '🔔',
                    title: 'Pusat Notifikasi',
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                      );
                    },
                  ),
                  _buildDivider(),
                  _buildMenuRow(
                    icon: '⚙️',
                    title: 'Pengaturan Notifikasi',
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const NotificationSettingsScreen()),
                      );
                    },
                  ),
                ],
              ),
            ),
            Container(height: 8, color: AppColors.divider),

            // Settings Group 2
            Container(
              color: Colors.white,
              child: Column(
                children: [
                  _buildMenuRow(
                    icon: '🏦',
                    title: 'Rekening Toko & Cara Bayar',
                    trailing: const StatusBadge(text: 'Resmi', type: BadgeType.green),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const RekeningScreen()),
                      );
                    },
                  ),
                  _buildDivider(),
                  _buildMenuRow(
                    icon: '💬',
                    title: 'Kritik & Saran',
                    trailing: const StatusBadge(text: 'B2C', type: BadgeType.blue),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const FeedbackListScreen()),
                      );
                    },
                  ),
                  _buildDivider(),
                  _buildMenuRow(
                    icon: '📱',
                    title: 'Hubungi WhatsApp Admin / CS',
                    trailing: const StatusBadge(text: 'Online', type: BadgeType.green),
                    onTap: () {
                      context.read<AuthProvider>().launchWhatsApp(
                        customMessage: 'Halo Admin ANHA Electrical, saya ingin bertanya...',
                      );
                    },
                  ),
                  if (user?.isAdmin == true) ...[
                    _buildDivider(),
                    _buildMenuRow(
                      icon: '⚡',
                      title: 'Input Produk (Admin Khusus)',
                      trailing: const StatusBadge(text: 'Admin', type: BadgeType.dark),
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AdminAddProductScreen()),
                        );
                      },
                    ),
                  ],
                  _buildDivider(),
                  _buildMenuRow(
                    icon: 'ℹ️',
                    title: 'Tentang Aplikasi',
                    onTap: () {
                      showAboutDialog(
                        context: context,
                        applicationName: 'ANHA Go',
                        applicationVersion: 'v1.0.7 (Build 12)',
                        applicationIcon: ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: Image.asset('assets/images/logo.png', width: 48, height: 48),
                        ),
                        applicationLegalese: '© 2026 ANHA Go. All rights reserved.',
                      );
                    },
                  ),
                  _buildDivider(),
                  _buildMenuRow(
                    icon: '⭐',
                    title: 'Beri Rating di Play Store',
                    onTap: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Membuka halaman rating di Google Play Store...')),
                      );
                    },
                  ),
                ],
              ),
            ),
            Container(height: 8, color: AppColors.divider),

            // Logout Row
            Container(
              color: Colors.white,
              child: _buildMenuRow(
                icon: '🚪',
                title: 'Keluar (Logout)',
                textColor: AppColors.danger,
                onTap: () {
                  _showLogoutDialog(context);
                },
              ),
            ),
            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }

  void _showLogoutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          title: const Text('Konfirmasi Keluar', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
          content: const Text('Apakah Anda yakin ingin keluar dari akun ANHA Go?', style: TextStyle(fontSize: 12)),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
              onPressed: () async {
                Navigator.pop(ctx);
                await context.read<AuthProvider>().logout();
                if (context.mounted) {
                  context.read<OrderProvider>().clear();
                  context.read<AddressProvider>().clear();
                  context.read<LoyaltyProvider>().clear();
                  context.read<NotificationProvider>().clear();
                  context.read<FeedbackProvider>().clear();
                }
                if (!context.mounted) return;
                Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(builder: (_) => const CodeLoginScreen()),
                  (route) => false,
                );
              },
              child: const Text('Keluar', style: TextStyle(color: Colors.white)),
            ),
          ],
        );
      },
    );
  }

  Widget _buildStatColumn(String value, String label, Color color) {
    return Expanded(
      child: Column(
        children: [
          Text(
            value,
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w900,
              color: color,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(fontSize: 9, color: AppColors.textLight),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuRow({
    required String icon,
    required String title,
    Widget? trailing,
    Color? textColor,
    VoidCallback? onTap,
  }) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(
          children: [
            Text(icon, style: const TextStyle(fontSize: 18)),
            const SizedBox(width: 14),
            Expanded(
              child: Text(
                title,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: textColor ?? AppColors.text,
                ),
              ),
            ),
            if (trailing != null) ...[
              trailing,
              const SizedBox(width: 6),
            ],
            const Icon(Icons.chevron_right, size: 18, color: AppColors.textLight),
          ],
        ),
      ),
    );
  }

  Widget _buildDivider() {
    return const Padding(
      padding: EdgeInsets.only(left: 48),
      child: Divider(height: 1, color: AppColors.divider),
    );
  }
}
