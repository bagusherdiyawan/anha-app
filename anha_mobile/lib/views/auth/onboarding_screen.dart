import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/storage/local_storage.dart';
import '../../widgets/custom_button.dart';
import 'code_login_screen.dart';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _controller = PageController();
  int _currentIndex = 0;

  final List<Map<String, dynamic>> _slides = [
    {
      'emoji': '🔌',
      'tag': 'Belanja Mudah',
      'title': 'Belanja Kebutuhan Listrik Kapan Saja',
      'desc': 'Ribuan produk listrik berkualitas tersedia. COD tersedia di seluruh area pengiriman kami.',
      'bg': const Color(0xFFEBF8FF),
      'cardBg': const Color(0xFFBFDBFE),
      'btnVariant': ButtonVariant.primary,
    },
    {
      'emoji': '⚡',
      'tag': 'Kumpulkan Poin',
      'title': 'Dapatkan Poin di Setiap Pembelian',
      'desc': 'Setiap Rp40.000 belanja = 1 Poin. Tukarkan poin dengan hadiah menarik di toko kami!',
      'bg': const Color(0xFFFEF9C3),
      'cardBg': const Color(0xFFFDE68A),
      'btnVariant': ButtonVariant.yellow,
    },
    {
      'emoji': '🚚',
      'tag': 'COD Terpercaya',
      'title': 'Bayar Tunai Saat Barang Tiba',
      'desc': 'Tidak perlu transfer di muka. Bayar langsung ke kurir ketika barang sudah ada di tangan Anda.',
      'bg': const Color(0xFFEBF4FC),
      'cardBg': const Color(0xFF93C5FD),
      'btnVariant': ButtonVariant.primary,
    },
  ];

  void _finishOnboarding() async {
    await LocalStorage.setOnboardingSeen(true);
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => const CodeLoginScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    final current = _slides[_currentIndex];

    return Scaffold(
      backgroundColor: current['bg'],
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            children: [
              // Top Bar with Skip
              Align(
                alignment: Alignment.centerRight,
                child: TextButton(
                  onPressed: _finishOnboarding,
                  child: const Text(
                    'Lewati',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: AppColors.primary,
                    ),
                  ),
                ),
              ),

              // Page View
              Expanded(
                child: PageView.builder(
                  controller: _controller,
                  onPageChanged: (idx) => setState(() => _currentIndex = idx),
                  itemCount: _slides.length,
                  itemBuilder: (context, index) {
                    final slide = _slides[index];
                    return Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        // Illustration card
                        Container(
                          width: double.infinity,
                          height: 190,
                          decoration: BoxDecoration(
                            color: slide['cardBg'],
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                slide['emoji'],
                                style: const TextStyle(fontSize: 54),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                slide['tag'],
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.primary,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 32),
                        Text(
                          slide['title'],
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w900,
                            color: AppColors.text,
                            height: 1.3,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          slide['desc'],
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontSize: 13,
                            color: AppColors.textMuted,
                            height: 1.5,
                          ),
                        ),
                      ],
                    );
                  },
                ),
              ),

              // Dot Indicators
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(_slides.length, (i) {
                  final active = i == _currentIndex;
                  return AnimatedContainer(
                    duration: const Duration(milliseconds: 250),
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    width: active ? 22 : 6,
                    height: 6,
                    decoration: BoxDecoration(
                      color: active ? AppColors.primary : const Color(0xFFBFDBFE),
                      borderRadius: BorderRadius.circular(4),
                    ),
                  );
                }),
              ),
              const SizedBox(height: 24),

              // Action Button
              CustomButton(
                text: _currentIndex == _slides.length - 1 ? 'Mulai Belanja' : 'Selanjutnya',
                variant: current['btnVariant'],
                onPressed: () {
                  if (_currentIndex < _slides.length - 1) {
                    _controller.nextPage(
                      duration: const Duration(milliseconds: 300),
                      curve: Curves.easeInOut,
                    );
                  } else {
                    _finishOnboarding();
                  }
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}
