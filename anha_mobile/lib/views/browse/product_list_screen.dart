import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../models/product_model.dart';
import '../../providers/cart_provider.dart';
import '../../providers/product_provider.dart';
import '../cart/cart_screen.dart';
import '../home/widgets/product_card.dart';
import 'product_search_screen.dart';

class ProductListScreen extends StatefulWidget {
  final String categoryTitle;
  final int categoryId;

  const ProductListScreen({
    super.key,
    required this.categoryTitle,
    required this.categoryId,
  });

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  String _activeFilter = 'Semua';
  bool _sortByPriceAsc = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ProductProvider>().fetchProductsByCategory(widget.categoryId);
    });
  }

  @override
  Widget build(BuildContext context) {
    final productProvider = context.watch<ProductProvider>();
    final cart = context.watch<CartProvider>();

    List<ProductModel> displayList = List.from(productProvider.categoryProducts);

    if (_activeFilter == 'Stok Tersedia') {
      displayList = displayList.where((p) => p.inStock).toList();
    } else if (_activeFilter == 'Harga Termurah') {
      displayList.sort((a, b) => a.harga.compareTo(b.harga));
    } else if (_activeFilter == 'Harga Tertinggi') {
      displayList.sort((a, b) => b.harga.compareTo(a.harga));
    }

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.text),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          widget.categoryTitle,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
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
      body: Column(
        children: [
          // Filter Chips Horizontal Strip
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            color: Colors.white,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterChip('Semua'),
                  const SizedBox(width: 8),
                  _buildFilterChip('Stok Tersedia'),
                  const SizedBox(width: 8),
                  _buildFilterChip(_sortByPriceAsc ? 'Harga Termurah' : 'Harga Tertinggi', onTap: () {
                    setState(() {
                      _sortByPriceAsc = !_sortByPriceAsc;
                      _activeFilter = _sortByPriceAsc ? 'Harga Termurah' : 'Harga Tertinggi';
                    });
                  }),
                ],
              ),
            ),
          ),
          const Divider(height: 1, color: AppColors.border),

          // Product Grid
          Expanded(
            child: productProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : displayList.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Text('📦', style: TextStyle(fontSize: 42)),
                            const SizedBox(height: 12),
                            Text(
                              'Belum ada produk di kategori ${widget.categoryTitle}',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: AppColors.textMuted,
                              ),
                            ),
                          ],
                        ),
                      )
                    : GridView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: displayList.length,
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          mainAxisSpacing: 12,
                          crossAxisSpacing: 12,
                          childAspectRatio: 0.72,
                        ),
                        itemBuilder: (context, index) {
                          return ProductCard(product: displayList[index]);
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, {VoidCallback? onTap}) {
    final active = _activeFilter == label;
    return InkWell(
      onTap: onTap ?? () => setState(() => _activeFilter = label),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: active ? AppColors.primary : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: active ? AppColors.primary : AppColors.border,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: active ? FontWeight.w700 : FontWeight.w600,
            color: active ? Colors.white : AppColors.text,
          ),
        ),
      ),
    );
  }
}
