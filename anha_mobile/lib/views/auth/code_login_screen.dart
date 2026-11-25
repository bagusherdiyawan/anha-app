import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/address_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/feedback_provider.dart';
import '../../providers/loyalty_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/order_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../home/main_navigation_screen.dart';

class CodeLoginScreen extends StatefulWidget {
  const CodeLoginScreen({super.key});

  @override
  State<CodeLoginScreen> createState() => _CodeLoginScreenState();
}

class _CodeLoginScreenState extends State<CodeLoginScreen> {
  final TextEditingController _codeController = TextEditingController();
  bool _isSubmitting = false;
  String? _errorMsg;

  @override
  void dispose() {
    _codeController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    final code = _codeController.text.trim();
    if (code.isEmpty) {
      setState(() => _errorMsg = 'Silakan masukkan Kode Akses Anda');
      return;
    }

    setState(() {
      _isSubmitting = true;
      _errorMsg = null;
    });

    final auth = context.read<AuthProvider>();
    final success = await auth.loginWithCode(code);

    if (!mounted) return;
    setState(() => _isSubmitting = false);

    if (success) {
      context.read<OrderProvider>().clear();
      context.read<AddressProvider>().clear();
      context.read<LoyaltyProvider>().clear();
      context.read<NotificationProvider>().clear();
      context.read<FeedbackProvider>().clear();

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Selamat datang, ${auth.currentUser?.name ?? 'Pelanggan'}!'),
          backgroundColor: AppColors.success,
        ),
      );
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
      );
    } else {
      setState(() {
        _errorMsg = auth.errorMessage ?? 'Kode salah atau akun belum aktif.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const SizedBox(height: 20),
              // App Brand Header
              ClipRRect(
                borderRadius: BorderRadius.circular(14),
                child: Image.asset(
                  'assets/images/logo.png',
                  width: 52,
                  height: 52,
                  fit: BoxFit.cover,
                ),
              ),
              const SizedBox(height: 10),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text(
                    'ANHA',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w900,
                      color: AppColors.primary,
                      letterSpacing: 1.5,
                    ),
                  ),
                  const SizedBox(width: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 6,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE60202),
                      borderRadius: BorderRadius.circular(5),
                    ),
                    child: const Text(
                      'GO',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 28),

              // Title and Subtitle
              const Align(
                alignment: Alignment.centerLeft,
                child: Text(
                  'Selamat Datang',
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    color: AppColors.text,
                  ),
                ),
              ),
              const SizedBox(height: 6),
              const Align(
                alignment: Alignment.centerLeft,
                child: Text(
                  'Masukkan Kode Akses yang telah digenerate oleh Admin Website untuk masuk ke akun Anda.',
                  style: TextStyle(
                    fontSize: 12,
                    color: AppColors.textMuted,
                    height: 1.5,
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // Info Box
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.primarySoft,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: const Row(
                  children: [
                    Text('🔑', style: TextStyle(fontSize: 20)),
                    SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'Akses berlaku untuk akun Ritel & Mitra Grosir B2B ANHA.',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: AppColors.primary,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Code Input Box
              CustomTextField(
                label: 'Kode Akses / Login Code',
                hint: 'Contoh: 1234 atau B2B-XXXX',
                controller: _codeController,
                prefixIcon: const Icon(Icons.vpn_key_rounded, size: 20, color: AppColors.primary),
                errorText: _errorMsg,
                suffixIcon: IconButton(
                  icon: const Icon(Icons.clear, size: 18, color: AppColors.textLight),
                  onPressed: () => _codeController.clear(),
                ),
              ),
              const SizedBox(height: 20),

              // Submit Button
              CustomButton(
                text: 'Masuk Sekarang',
                variant: ButtonVariant.yellow,
                isLoading: _isSubmitting,
                onPressed: _handleLogin,
              ),
              const SizedBox(height: 24),

              // Divider
              const Row(
                children: [
                  Expanded(child: Divider(color: AppColors.border)),
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 12),
                    child: Text(
                      'BANTUAN AKSES',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textLight,
                        letterSpacing: 1,
                      ),
                    ),
                  ),
                  Expanded(child: Divider(color: AppColors.border)),
                ],
              ),
              const SizedBox(height: 20),

              // Contact WhatsApp Admin Button
              CustomButton(
                text: 'Hubungi Admin via WhatsApp',
                variant: ButtonVariant.outline,
                icon: const Text('💬', style: TextStyle(fontSize: 16)),
                onPressed: () {
                  context.read<AuthProvider>().launchWhatsApp(
                    customMessage: 'Halo Admin ANHA Electrical, saya ingin meminta Kode Akses untuk login aplikasi mobile.',
                  );
                },
              ),
              const SizedBox(height: 16),
              const Text(
                'Belum punya kode akses? Tim kami akan segera membuatkan akun pelanggan untuk Anda.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 11,
                  color: AppColors.textMuted,
                  height: 1.4,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
