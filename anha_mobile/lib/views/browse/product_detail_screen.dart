import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/product_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/product_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/status_badge.dart';
import '../cart/cart_screen.dart';
import 'product_search_screen.dart';

class ProductDetailScreen extends StatefulWidget {
  final ProductModel product;

  const ProductDetailScreen({super.key, required this.product});

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  late ProductModel _product;
  late final PageController _pageController;
  int _selectedQty = 1;
  int _activePhotoIndex = 0;
  bool _isDescriptionExpanded = false;
  bool _isSubmittingCart = false;

  @override
  void initState() {
    super.initState();
    _product = widget.product;
    _pageController = PageController();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadLatestDetail());
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _loadLatestDetail() async {
    if (_product.id <= 0) return;
    ProductProvider? provider;
    try {
      provider = context.read<ProductProvider>();
    } catch (_) {
      return;
    }
    final detail = await provider.fetchProductDetail(_product.id);
    if (!mounted || detail == null) return;
    setState(() {
      _product = _product.mergeWithDetail(detail);
      final photoCount = _product.fullPhotoUrls.length;
      if (photoCount > 0 && _activePhotoIndex >= photoCount) {
        _activePhotoIndex = 0;
        if (_pageController.hasClients) {
          _pageController.jumpToPage(0);
        }
      }
      if (_product.stock > 0 && _selectedQty > _product.stock) {
        _selectedQty = _product.stock;
      }
    });
  }

  void _selectPhoto(int index) {
    setState(() => _activePhotoIndex = index);
    if (_pageController.hasClients) {
      _pageController.animateToPage(
        index,
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeInOut,
      );
    }
  }

  Future<void> _handleAddToCart(CartProvider cart, {required bool goToCart}) async {
    if (_isSubmittingCart) return;
    setState(() => _isSubmittingCart = true);

    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);
    final error = await cart.addToCart(_product, qty: _selectedQty);

    if (!mounted) return;
    setState(() => _isSubmittingCart = false);

    if (error != null) {
      messenger.showSnackBar(
        SnackBar(
          content: Text(error),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    if (goToCart) {
      navigator.push(
        MaterialPageRoute(builder: (_) => const CartScreen()),
      );
    } else {
      messenger.showSnackBar(
        SnackBar(
          content: Text('${_product.namaBarang} ditambahkan ke keranjang!'),
          backgroundColor: AppColors.primary,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final unitPrice = _product.harga;
    final totalPrice = unitPrice * _selectedQty;
    final photos = _product.fullPhotoUrls;

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
          'ANHA GO',
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w900,
            color: AppColors.primary,
            letterSpacing: 0.5,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.search, color: AppColors.text),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const ProductSearchScreen()),
              );
            },
          ),
          IconButton(
            icon: Stack(
              clipBehavior: Clip.none,
              children: [
                const Icon(Icons.shopping_cart_outlined, color: AppColors.text),
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
                        cart.itemCount.toString(),
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
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image Gallery Slider with Overlay Badges
            Container(
              height: 220,
              width: double.infinity,
              color: const Color(0xFFE5E7EB),
              child: Stack(
                children: [
                  if (photos.isNotEmpty)
                    Positioned.fill(
                      child: PageView.builder(
                        controller: _pageController,
                        itemCount: photos.length,
                        onPageChanged: (index) {
                          setState(() => _activePhotoIndex = index);
                        },
                        itemBuilder: (context, index) {
                          return Image.network(
                            photos[index],
                            fit: BoxFit.contain,
                            errorBuilder: (_, __, ___) => Center(
                              child: Text(
                                _getEmoji(_product.namaBarang),
                                style: const TextStyle(fontSize: 70),
                              ),
                            ),
                          );
                        },
                      ),
                    )
                  else
                    Center(
                      child: Text(
                        _getEmoji(_product.namaBarang),
                        style: const TextStyle(fontSize: 70),
                      ),
                    ),
                  Positioned(
                    top: 10,
                    left: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.yellow,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.star, size: 12, color: AppColors.text),
                          const SizedBox(width: 4),
                          Text(
                            _product.rating > 0
                                ? 'Rating: ${_product.rating.toStringAsFixed(1)}'
                                : 'Original ANHA',
                            style: const TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w800,
                              color: AppColors.text,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  if (photos.length > 1) ...[
                    Positioned(
                      bottom: 10,
                      left: 0,
                      right: 0,
                      child: IgnorePointer(
                        child: Center(
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.black.withValues(alpha: 0.35),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: List.generate(photos.length, (i) {
                                final active = i == _activePhotoIndex;
                                return Container(
                                  margin: const EdgeInsets.symmetric(horizontal: 2.5),
                                  width: active ? 14 : 6,
                                  height: 5,
                                  decoration: BoxDecoration(
                                    color: active
                                        ? AppColors.yellow
                                        : Colors.white.withValues(alpha: 0.75),
                                    borderRadius: BorderRadius.circular(3),
                                  ),
                                );
                              }),
                            ),
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 10,
                      right: 12,
                      child: IgnorePointer(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.55),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            '${_activePhotoIndex + 1} / ${photos.length}',
                            style: const TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),

            // Image Thumbnails Row (if multiple fotos)
            if (photos.length > 1) ...[
              Padding(
                padding: const EdgeInsets.all(12),
                child: SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: List.generate(photos.length, (index) {
                      final active = index == _activePhotoIndex;
                      return InkWell(
                        onTap: () => _selectPhoto(index),
                        child: Container(
                          width: 46,
                          height: 46,
                          margin: const EdgeInsets.only(right: 8),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF3F4F6),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: active ? AppColors.primary : AppColors.border,
                              width: active ? 2 : 1,
                            ),
                          ),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(5),
                            child: Image.network(
                              photos[index],
                              fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => Center(
                                child: Text(
                                  _getEmoji(_product.namaBarang),
                                  style: const TextStyle(fontSize: 18),
                                ),
                              ),
                            ),
                          ),
                        ),
                      );
                    }),
                  ),
                ),
              ),
              const Divider(height: 1, color: AppColors.divider),
            ],

            // Title, Code & Price
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      StatusBadge(
                        text: _product.kodeBarang ?? 'PROD-${_product.id}',
                        type: BadgeType.gray,
                      ),
                      if (_product.inStock)
                        const StatusBadge(text: '✓ STOK TERSEDIA', type: BadgeType.green)
                      else
                        const StatusBadge(text: 'STOK HABIS', type: BadgeType.red),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _product.namaBarang,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      color: AppColors.text,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.baseline,
                    textBaseline: TextBaseline.alphabetic,
                    children: [
                      Text(
                        CurrencyFormatter.format(unitPrice),
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w900,
                          color: AppColors.accent,
                        ),
                      ),
                      if (_product.satuan != null && _product.satuan!.isNotEmpty)
                        Text(
                          ' / ${_product.satuan}',
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppColors.textMuted,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Sisa stok: ${_product.stock} ${_product.satuan ?? 'pcs'}',
                    style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
                  ),
                ],
              ),
            ),
            Container(height: 8, color: AppColors.divider),

            // Description
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'DESKRIPSI PRODUK',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: AppColors.text,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _product.deskripsi != null && _product.deskripsi!.isNotEmpty
                        ? _product.deskripsi!
                        : 'Belum ada deskripsi lengkap untuk produk ini.',
                    maxLines: _isDescriptionExpanded ? null : 3,
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppColors.textMuted,
                      height: 1.5,
                    ),
                  ),
                  InkWell(
                    onTap: () => setState(() => _isDescriptionExpanded = !_isDescriptionExpanded),
                    child: Padding(
                      padding: const EdgeInsets.only(top: 4),
                      child: Text(
                        _isDescriptionExpanded ? 'Tutup deskripsi' : 'Baca selengkapnya',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: AppColors.primary,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Container(height: 8, color: AppColors.divider),

            // Quantity Selection
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Jumlah Pembelian',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppColors.text,
                    ),
                  ),
                  const SizedBox(height: 10),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        decoration: BoxDecoration(
                          border: Border.all(color: AppColors.border),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          children: [
                            IconButton(
                              icon: const Icon(Icons.remove, size: 16),
                              onPressed: _selectedQty > 1
                                  ? () => setState(() => _selectedQty--)
                                  : null,
                            ),
                            Container(
                              width: 40,
                              alignment: Alignment.center,
                              child: Text(
                                _selectedQty.toString(),
                                style: const TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.add, size: 16),
                              onPressed: _selectedQty < _product.stock
                                  ? () => setState(() => _selectedQty++)
                                  : null,
                            ),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          const Text(
                            'Total Harga:',
                            style: TextStyle(fontSize: 10, color: AppColors.textMuted),
                          ),
                          Text(
                            CurrencyFormatter.format(totalPrice),
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w900,
                              color: AppColors.accent,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.border)),
        ),
        child: Row(
          children: [
            // WhatsApp chat inquiry button
            InkWell(
              onTap: () {
                context.read<AuthProvider>().launchWhatsApp(
                  customMessage: 'Halo Admin ANHA, saya ingin bertanya tentang ${_product.namaBarang} (Kode: ${_product.kodeBarang ?? '-'}).',
                );
              },
              borderRadius: BorderRadius.circular(8),
              child: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  border: Border.all(color: AppColors.border),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Center(
                  child: Text('💬', style: TextStyle(fontSize: 20)),
                ),
              ),
            ),
            const SizedBox(width: 8),

            // Tambah Keranjang
            Expanded(
              child: CustomButton(
                text: 'Keranjang',
                variant: ButtonVariant.outline,
                onPressed: (_product.inStock && !_isSubmittingCart)
                    ? () => _handleAddToCart(cart, goToCart: false)
                    : null,
              ),
            ),
            const SizedBox(width: 8),

            // Beli Sekarang
            Expanded(
              child: CustomButton(
                text: 'Beli Sekarang',
                variant: ButtonVariant.yellow,
                onPressed: (_product.inStock && !_isSubmittingCart)
                    ? () => _handleAddToCart(cart, goToCart: true)
                    : null,
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getEmoji(String name) {
    final lower = name.toLowerCase();
    if (lower.contains('kabel')) return '🔌';
    if (lower.contains('lampu') || lower.contains('led')) return '💡';
    if (lower.contains('mcb')) return '⚡';
    if (lower.contains('saklar')) return '🔲';
    if (lower.contains('stop') || lower.contains('kontak')) return '🔧';
    return '⚡';
  }
}
