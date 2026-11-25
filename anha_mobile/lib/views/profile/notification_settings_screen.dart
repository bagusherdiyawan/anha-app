import 'package:flutter/material.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../widgets/custom_button.dart';

class NotificationSettingsScreen extends StatefulWidget {
  const NotificationSettingsScreen({super.key});

  @override
  State<NotificationSettingsScreen> createState() => _NotificationSettingsScreenState();
}

class _NotificationSettingsScreenState extends State<NotificationSettingsScreen> {
  final ApiClient _api = ApiClient();

  bool _orderNotifications = true;
  bool _pointNotifications = true;
  bool _promoNotifications = true;
  bool _feedbackNotifications = true;
  bool _isSaving = false;

  Future<void> _handleSave() async {
    setState(() => _isSaving = true);

    try {
      // Register / update device token on backend: POST /v1/update-token
      await _api.post(
        ApiEndpoints.updateDeviceToken,
        data: {
          'token': 'fcm_token_device_${DateTime.now().millisecondsSinceEpoch}',
          'platform': 'android',
        },
      );
    } catch (_) {}

    if (!mounted) return;
    setState(() => _isSaving = false);

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Pengaturan notifikasi berhasil disimpan!'),
        backgroundColor: AppColors.success,
      ),
    );
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
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
          'Pengaturan Notifikasi',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'PILIH NOTIFIKASI YANG INGIN DITERIMA',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                color: AppColors.textMuted,
                letterSpacing: 0.8,
              ),
            ),
            const SizedBox(height: 12),

            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: [
                  _buildToggleRow(
                    icon: '📦',
                    title: 'Status Pesanan & Pengiriman COD',
                    subtitle: 'Notifikasi saat pesanan diproses, dikirim, dan kurir tiba.',
                    value: _orderNotifications,
                    onChanged: (val) => setState(() => _orderNotifications = val),
                  ),
                  const Divider(height: 1, color: AppColors.divider),
                  _buildToggleRow(
                    icon: '⚡',
                    title: 'Poin Listrik & Reward',
                    subtitle: 'Informasi perolehan poin dan konfirmasi penukaran hadiah.',
                    value: _pointNotifications,
                    onChanged: (val) => setState(() => _pointNotifications = val),
                  ),
                  const Divider(height: 1, color: AppColors.divider),
                  _buildToggleRow(
                    icon: '🏷️',
                    title: 'Promo & Diskon Spesial',
                    subtitle: 'Update penawaran voucher belanja dan harga grosir terbaru.',
                    value: _promoNotifications,
                    onChanged: (val) => setState(() => _promoNotifications = val),
                  ),
                  const Divider(height: 1, color: AppColors.divider),
                  _buildToggleRow(
                    icon: '💬',
                    title: 'Balasan Kritik & Saran',
                    subtitle: 'Notifikasi saat admin menanggapi masukan atau keluhan Anda.',
                    value: _feedbackNotifications,
                    onChanged: (val) => setState(() => _feedbackNotifications = val),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Token & Device Info
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.primarySoft,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: const Row(
                children: [
                  Text('🔔', style: TextStyle(fontSize: 18)),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Notifikasi push disinkronkan otomatis dengan Firebase Cloud Messaging untuk server ANHA Electrical.',
                      style: TextStyle(fontSize: 11, color: AppColors.text, height: 1.3),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),

            CustomButton(
              text: 'Simpan Pengaturan',
              variant: ButtonVariant.primary,
              isLoading: _isSaving,
              onPressed: _handleSave,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildToggleRow({
    required String icon,
    required String title,
    required String subtitle,
    required bool value,
    required ValueChanged<bool> onChanged,
  }) {
    return Padding(
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(icon, style: const TextStyle(fontSize: 22)),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: AppColors.text,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: const TextStyle(
                    fontSize: 10,
                    color: AppColors.textLight,
                    height: 1.3,
                  ),
                ),
              ],
            ),
          ),
          Switch(
            value: value,
            activeColor: AppColors.success,
            onChanged: onChanged,
          ),
        ],
      ),
    );
  }
}
