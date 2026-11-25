import 'dart:async';
import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';

class BannerCarousel extends StatefulWidget {
  final List<String> banners;

  const BannerCarousel({super.key, this.banners = const []});

  @override
  State<BannerCarousel> createState() => _BannerCarouselState();
}

class _BannerCarouselState extends State<BannerCarousel> {
  final PageController _pageController = PageController();
  int _currentPage = 0;
  Timer? _timer;

  final List<Map<String, String>> _defaultBanners = [
    {
      'tag': 'PROMO BULAN INI',
      'title': 'Diskon 25% Perlengkapan & Instalasi',
      'action': 'Cek Sekarang',
      'emoji': '⚡',
    },
    {
      'tag': 'GRATIS ONGKIR COD',
      'title': 'Pengiriman Langsung Sampai di Tempat',
      'action': 'Pesan Sekarang',
      'emoji': '🚚',
    },
    {
      'tag': 'LOYALTY REWARD',
      'title': 'Kumpulkan Poin Tiap Belanja Rp40.000',
      'action': 'Tukar Hadiah',
      'emoji': '🎁',
    },
  ];

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (!mounted) return;
      final count = widget.banners.isNotEmpty ? widget.banners.length : _defaultBanners.length;
      if (_currentPage < count - 1) {
        _currentPage++;
      } else {
        _currentPage = 0;
      }
      if (_pageController.hasClients) {
        _pageController.animateToPage(
          _currentPage,
          duration: const Duration(milliseconds: 350),
          curve: Curves.easeInOut,
        );
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final count = widget.banners.isNotEmpty ? widget.banners.length : _defaultBanners.length;

    return Column(
      children: [
        SizedBox(
          height: 120,
          child: PageView.builder(
            controller: _pageController,
            onPageChanged: (idx) => setState(() => _currentPage = idx),
            itemCount: count,
            itemBuilder: (context, index) {
              if (widget.banners.isNotEmpty) {
                final bannerUrl = widget.banners[index];
                return Container(
                  margin: const EdgeInsets.symmetric(horizontal: 16),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.08),
                        blurRadius: 8,
                        offset: const Offset(0, 3),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.network(
                      bannerUrl,
                      fit: BoxFit.cover,
                      width: double.infinity,
                      errorBuilder: (_, __, ___) => _buildFallbackBanner(_defaultBanners[index % _defaultBanners.length]),
                    ),
                  ),
                );
              }
              final item = _defaultBanners[index % _defaultBanners.length];
              return _buildFallbackBanner(item);
            },
          ),
        ),
        const SizedBox(height: 8),
        // Indicator Dots
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(count, (i) {
            final active = i == _currentPage;
            return Container(
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: active ? 16 : 5,
              height: 4,
              decoration: BoxDecoration(
                color: active ? AppColors.primary : const Color(0xFFD1D5DB),
                borderRadius: BorderRadius.circular(2),
              ),
            );
          }),
        ),
      ],
    );
  }

  Widget _buildFallbackBanner(Map<String, String> item) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppColors.primary,
            Color(0xFF2B7FC4),
          ],
        ),
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withOpacity(0.2),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            right: -10,
            bottom: -10,
            child: Text(
              item['emoji']!,
              style: TextStyle(
                fontSize: 70,
                color: Colors.white.withOpacity(0.12),
              ),
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppColors.yellow,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      item['tag']!,
                      style: const TextStyle(
                        fontSize: 8,
                        fontWeight: FontWeight.w800,
                        color: AppColors.text,
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    item['title']!,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w900,
                      color: Colors.white,
                      height: 1.2,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  item['action']!,
                  style: const TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.w800,
                    color: AppColors.primary,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
