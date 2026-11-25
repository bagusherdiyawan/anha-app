import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/product_provider.dart';
import '../browse/product_search_screen.dart';
import '../cart/cart_screen.dart';
import '../profile/notifications_screen.dart';
import 'widgets/banner_carousel.dart';
import 'widgets/category_grid.dart';
import 'widgets/floating_whatsapp_button.dart';
import 'widgets/product_card.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ProductProvider>().fetchBanners();
      context.read<ProductProvider>().fetchCategories();
      context.read<ProductProvider>().fetchLandingProducts();
      context.read<CartProvider>().fetchCart();
      context.read<NotificationProvider>().fetchUnreadCount();
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final productProvider = context.watch<ProductProvider>();
    final cart = context.watch<CartProvider>();

    return Scaffold(
      backgroundColor: AppColors.background,
      floatingActionButton: const FloatingWhatsAppButton(),
      body: SafeArea(
        child: Column(
          children: [
            // App Header (matching wireframe .ah)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              color: Colors.white,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(6),
                        child: Image.asset(
                          'assets/images/logo.png',
                          width: 28,
                          height: 28,
                          fit: BoxFit.cover,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Text(
                                'ANHA',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w900,
                                  color: AppColors.primary,
                                  letterSpacing: 0.5,
                                ),
                              ),
                              const SizedBox(width: 4),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 5,
                                  vertical: 1,
                                ),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFE60202),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: const Text(
                                  'GO',
                                  style: TextStyle(
                                    fontSize: 9,
                                    fontWeight: FontWeight.w900,
                                    color: Colors.white,
                                    letterSpacing: 0.5,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          Text(
                            'Halo, ${auth.currentUser?.name.split(' ').first ?? 'Pelanggan'}!',
                            style: const TextStyle(
                              fontSize: 10,
                              color: AppColors.textMuted,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      // Notifications icon
                      Consumer<NotificationProvider>(
                        builder: (context, notifProv, _) {
                          return IconButton(
                            icon: Stack(
                              clipBehavior: Clip.none,
                              children: [
                                const Icon(Icons.notifications_outlined, color: AppColors.text, size: 22),
                                if (notifProv.unreadCount > 0)
                                  Positioned(
                                    top: -2,
                                    right: -2,
                                    child: Container(
                                      width: 8,
                                      height: 8,
                                      decoration: const BoxDecoration(
                                        color: AppColors.danger,
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                              );
                            },
                          );
                        },
                      ),
                      // Cart icon
                      IconButton(
                        icon: Stack(
                          clipBehavior: Clip.none,
                          children: [
                            const Icon(Icons.shopping_cart_outlined, color: AppColors.text, size: 22),
                            if (cart.itemCount > 0)
                              Positioned(
                                top: -4,
                                right: -4,
                                child: Container(
                                  padding: const EdgeInsets.all(3),
                                  decoration: const BoxDecoration(
                                    color: AppColors.danger,
                                    shape: BoxShape.circle,
                                  ),
                                  child: Text(
                                    cart.itemCount > 9 ? '9+' : cart.itemCount.toString(),
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 8,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                ),
                              ),
                          ],
                        ),
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => const CartScreen()),
                          );
                        },
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // Search Bar Button
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              child: InkWell(
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const ProductSearchScreen()),
                  );
                },
                borderRadius: BorderRadius.circular(10),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: const Row(
                    children: [
                      Icon(Icons.search, size: 20, color: AppColors.textLight),
                      SizedBox(width: 8),
                      Text(
                        'Cari perlengkapan listrik atau kode barang...',
                        style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textLight,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Main Scrollable Area
            Expanded(
              child: RefreshIndicator(
                onRefresh: () async {
                  await productProvider.fetchBanners();
                  await productProvider.fetchCategories();
                  await productProvider.fetchLandingProducts();
                },
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.only(bottom: 80),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Banner Carousel
                      BannerCarousel(banners: productProvider.banners),
                      const SizedBox(height: 18),

                      // Category Grid
                      CategoryGrid(categories: productProvider.categories),
                      const SizedBox(height: 20),

                      // Product Section Header
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'Produk Terbaru',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w800,
                                color: AppColors.text,
                              ),
                            ),
                            if (auth.currentUser?.isB2B == true)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppColors.primarySoft,
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  'HARGA GROSIR B2B',
                                  style: TextStyle(
                                    fontSize: 8,
                                    fontWeight: FontWeight.w800,
                                    color: AppColors.primary,
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Product Grid
                      if (productProvider.isLoading && productProvider.landingProducts.isEmpty)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.all(32),
                            child: CircularProgressIndicator(),
                          ),
                        )
                      else
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          child: GridView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: productProvider.landingProducts.length,
                            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              mainAxisSpacing: 12,
                              crossAxisSpacing: 12,
                              childAspectRatio: 0.72,
                            ),
                            itemBuilder: (context, index) {
                              final prod = productProvider.landingProducts[index];
                              return ProductCard(product: prod);
                            },
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
