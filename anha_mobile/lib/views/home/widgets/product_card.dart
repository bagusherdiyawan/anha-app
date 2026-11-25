import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/utils/currency_formatter.dart';
import '../../../models/product_model.dart';
import '../../../providers/cart_provider.dart';
import '../../browse/product_detail_screen.dart';

class ProductCard extends StatelessWidget {
  final ProductModel product;

  const ProductCard({super.key, required this.product});

  String _getCategoryEmoji(String name) {
    final lower = name.toLowerCase();
    if (lower.contains('kabel')) return '🔌';
    if (lower.contains('lampu') || lower.contains('led')) return '💡';
    if (lower.contains('mcb')) return '⚡';
    if (lower.contains('saklar')) return '🔲';
    if (lower.contains('stop') || lower.contains('kontak')) return '🔧';
    if (lower.contains('panel')) return '🗂';
    return '⚡';
  }

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => ProductDetailScreen(product: product),
          ),
        );
      },
      borderRadius: BorderRadius.circular(10),
      child: Container(
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
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image / Icon Container
            Container(
              width: double.infinity,
              height: 90,
              color: const Color(0xFFF3F4F6),
              child: Stack(
                children: [
                  if (product.fullPhotoUrl != null)
                    Positioned.fill(
                      child: Image.network(
                        product.fullPhotoUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Center(
                          child: Text(
                            _getCategoryEmoji(product.namaBarang),
                            style: const TextStyle(fontSize: 38),
                          ),
                        ),
                      ),
                    )
                  else
                    Center(
                      child: Text(
                        _getCategoryEmoji(product.namaBarang),
                        style: const TextStyle(fontSize: 38),
                      ),
                    ),
                  if (product.stock <= 5 && product.stock > 0)
                    Positioned(
                      top: 6,
                      left: 6,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.warningSoft,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'STOK TERBATAS',
                          style: TextStyle(
                            fontSize: 7,
                            fontWeight: FontWeight.w800,
                            color: AppColors.yellowDark,
                          ),
                        ),
                      ),
                    )
                  else if (!product.inStock)
                    Positioned(
                      top: 6,
                      left: 6,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.dangerSoft,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'HABIS',
                          style: TextStyle(
                            fontSize: 7,
                            fontWeight: FontWeight.w800,
                            color: AppColors.danger,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),

            // Product Info
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.categoryName?.toUpperCase() ?? 'ELEKTRIKAL',
                    style: const TextStyle(
                      fontSize: 8,
                      fontWeight: FontWeight.w700,
                      color: AppColors.textLight,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    product.namaBarang,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: AppColors.text,
                      height: 1.2,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              CurrencyFormatter.format(product.harga),
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w900,
                                color: AppColors.accent,
                              ),
                            ),
                            if (product.satuan != null)
                              Text(
                                '/${product.satuan}',
                                style: const TextStyle(
                                  fontSize: 8,
                                  color: AppColors.textMuted,
                                ),
                              ),
                          ],
                        ),
                      ),
                      // Add to cart quick button
                      Builder(
                        builder: (context) {
                          final isAdding = context.select<CartProvider, bool>(
                            (cart) => cart.isAddingProduct(product.id),
                          );
                          final canAdd = product.inStock && !isAdding;
                          return InkWell(
                            onTap: canAdd
                                ? () async {
                                    final messenger = ScaffoldMessenger.of(context);
                                    final error = await context.read<CartProvider>().addToCart(product);
                                    if (!context.mounted) return;
                                    messenger.showSnackBar(
                                      SnackBar(
                                        content: Text(
                                          error ?? '${product.namaBarang} ditambahkan ke keranjang!',
                                        ),
                                        duration: const Duration(seconds: 2),
                                        backgroundColor: error != null ? AppColors.danger : AppColors.primary,
                                      ),
                                    );
                                  }
                                : null,
                            borderRadius: BorderRadius.circular(12),
                            child: Container(
                              width: 24,
                              height: 24,
                              decoration: BoxDecoration(
                                color: product.inStock
                                    ? (isAdding ? AppColors.yellowSoft : AppColors.yellow)
                                    : const Color(0xFFE5E7EB),
                                shape: BoxShape.circle,
                              ),
                              child: Center(
                                child: isAdding
                                    ? const SizedBox(
                                        width: 12,
                                        height: 12,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2,
                                          color: AppColors.text,
                                        ),
                                      )
                                    : Icon(
                                        Icons.add,
                                        size: 16,
                                        color: product.inStock ? AppColors.text : AppColors.textLight,
                                      ),
                              ),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
