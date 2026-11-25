import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/product_model.dart';
import '../../providers/product_provider.dart';
import '../../widgets/status_badge.dart';
import 'product_detail_screen.dart';

class ProductSearchScreen extends StatefulWidget {
  const ProductSearchScreen({super.key});

  @override
  State<ProductSearchScreen> createState() => _ProductSearchScreenState();
}

class _ProductSearchScreenState extends State<ProductSearchScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearch(String query) {
    context.read<ProductProvider>().searchProducts(query);
  }

  @override
  Widget build(BuildContext context) {
    final productProvider = context.watch<ProductProvider>();
    final results = productProvider.searchResults;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.text),
          onPressed: () => Navigator.pop(context),
        ),
        title: Container(
          height: 38,
          decoration: BoxDecoration(
            color: const Color(0xFFF3F4F6),
            borderRadius: BorderRadius.circular(8),
          ),
          child: TextField(
            controller: _searchController,
            autofocus: true,
            onChanged: _onSearch,
            style: const TextStyle(fontSize: 13, color: AppColors.text),
            decoration: InputDecoration(
              isDense: true,
              hintText: 'Cari nama atau kode 6 digit...',
              hintStyle: const TextStyle(fontSize: 12, color: AppColors.textLight),
              prefixIcon: const Icon(Icons.search, size: 18, color: AppColors.textLight),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.close, size: 16, color: AppColors.textLight),
                      onPressed: () {
                        _searchController.clear();
                        _onSearch('');
                      },
                    )
                  : null,
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(vertical: 10),
            ),
          ),
        ),
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (_searchController.text.isNotEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              color: Colors.white,
              child: Text(
                'Ditemukan ${results.length} produk untuk "${_searchController.text}"',
                style: const TextStyle(fontSize: 11, color: AppColors.textMuted),
              ),
            ),
          Expanded(
            child: productProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : results.isEmpty && _searchController.text.isNotEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Text('🔍', style: TextStyle(fontSize: 36)),
                            const SizedBox(height: 8),
                            Text(
                              'Produk "${_searchController.text}" tidak ditemukan',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: AppColors.textMuted,
                              ),
                            ),
                          ],
                        ),
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: results.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 10),
                        itemBuilder: (context, index) {
                          final item = results[index];
                          return _buildSearchResultItem(context, item);
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchResultItem(BuildContext context, ProductModel product) {
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
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          children: [
            Container(
              width: 54,
              height: 54,
              decoration: BoxDecoration(
                color: const Color(0xFFF3F4F6),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Center(
                child: Text('🔌', style: TextStyle(fontSize: 24)),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.kodeBarang ?? 'KODE-000',
                    style: const TextStyle(
                      fontSize: 8,
                      fontWeight: FontWeight.w700,
                      color: AppColors.textLight,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    product.namaBarang,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppColors.text,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        CurrencyFormatter.format(product.harga),
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w900,
                          color: AppColors.accent,
                        ),
                      ),
                      if (product.inStock)
                        const StatusBadge(text: 'TERSEDIA', type: BadgeType.green)
                      else
                        const StatusBadge(text: 'HABIS', type: BadgeType.red),
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
