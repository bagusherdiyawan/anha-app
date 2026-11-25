import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../models/notification_model.dart';
import '../../providers/notification_provider.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationProvider>().fetchNotifications();
    });
  }

  @override
  Widget build(BuildContext context) {
    final notifProvider = context.watch<NotificationProvider>();
    final notifications = notifProvider.notifications;

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.text),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Notifikasi',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
        actions: [
          if (notifProvider.unreadCount > 0)
            TextButton(
              onPressed: () {
                notifProvider.markAllAsRead();
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Semua notifikasi ditandai telah dibaca.')),
                );
              },
              child: const Text(
                'Baca Semua',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: AppColors.primary,
                ),
              ),
            ),
        ],
      ),
      body: notifProvider.isLoading
          ? const Center(child: CircularProgressIndicator())
          : notifications.isEmpty
              ? const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text('🔔', style: TextStyle(fontSize: 40)),
                      SizedBox(height: 12),
                      Text(
                        'Belum Ada Notifikasi',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.text,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'Pembaruan pesanan & promo akan muncul di sini',
                        style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: () => notifProvider.fetchNotifications(),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: notifications.length,
                    itemBuilder: (context, index) {
                      final item = notifications[index];
                      return _buildNotifCard(context, item, notifProvider);
                    },
                  ),
                ),
    );
  }

  Widget _buildNotifCard(BuildContext context, NotificationModel item, NotificationProvider provider) {
    final bool isUnread = !item.isRead;

    return InkWell(
      onTap: () {
        if (isUnread) {
          provider.markAsRead(item.id);
        }
      },
      borderRadius: BorderRadius.circular(10),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isUnread ? AppColors.primarySoft : Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isUnread ? const Color(0xFFBFDBFE) : AppColors.border,
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: isUnread ? AppColors.primary : const Color(0xFFF3F4F6),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Center(
                child: Text(item.iconEmoji, style: const TextStyle(fontSize: 18)),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.title,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: isUnread ? FontWeight.w800 : FontWeight.w700,
                      color: AppColors.text,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    item.body,
                    style: const TextStyle(
                      fontSize: 11,
                      color: AppColors.textMuted,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    item.timeFormatted,
                    style: const TextStyle(fontSize: 9, color: AppColors.textLight),
                  ),
                ],
              ),
            ),
            if (isUnread)
              Container(
                margin: const EdgeInsets.only(top: 4, left: 4),
                width: 8,
                height: 8,
                decoration: const BoxDecoration(
                  color: AppColors.primary,
                  shape: BoxShape.circle,
                ),
              ),
          ],
        ),
      ),
    );
  }
}
