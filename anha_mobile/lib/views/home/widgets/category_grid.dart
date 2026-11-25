import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../models/category_model.dart';
import '../../browse/categories_screen.dart';
import '../../browse/product_list_screen.dart';

class CategoryGrid extends StatelessWidget {
  final List<CategoryModel> categories;

  const CategoryGrid({super.key, this.categories = const []});

  static String getCategoryEmoji(String name) {
    final lower = name.toLowerCase();
    if (lower.contains('kabel')) return '🔌';
    if (lower.contains('stop') || lower.contains('steker')) return '🔧';
    if (lower.contains('mcb') || lower.contains('pengaman') || lower.contains('panel')) return '⚡';
    if (lower.contains('lampu') || lower.contains('cahaya') || lower.contains('pencahayaan')) return '💡';
    if (lower.contains('saklar') || lower.contains('sakelar')) return '🔲';
    if (lower.contains('fitting')) return '🔩';
    if (lower.contains('baterai')) return '🔋';
    return '⚡';
  }

  static Widget buildCategoryIcon(
    CategoryModel category, {
    double size = 46,
    double emojiSize = 20,
  }) {
    final emoji = getCategoryEmoji(category.nama);
    final photoUrl = category.fullPhotoUrl;

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: AppColors.primarySoft,
        borderRadius: BorderRadius.circular(12),
      ),
      clipBehavior: Clip.antiAlias,
      child: photoUrl != null
          ? Image.network(
              photoUrl,
              width: size,
              height: size,
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => Center(
                child: Text(
                  emoji,
                  style: TextStyle(fontSize: emojiSize),
                ),
              ),
            )
          : Center(
              child: Text(
                emoji,
                style: TextStyle(fontSize: emojiSize),
              ),
            ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (categories.isEmpty) {
      return const SizedBox.shrink();
    }

    final List<CategoryModel?> displayItems = [
      ...categories.take(7),
      null,
    ];

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Kategori Terpopuler',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                  color: AppColors.text,
                ),
              ),
              InkWell(
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const CategoriesScreen()),
                  );
                },
                child: const Text(
                  'Lihat Semua',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppColors.primary,
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14),
          child: GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: displayItems.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              mainAxisSpacing: 10,
              crossAxisSpacing: 8,
              childAspectRatio: 0.85,
            ),
            itemBuilder: (context, index) {
              final cat = displayItems[index];
              return InkWell(
                onTap: () {
                  if (cat == null) {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const CategoriesScreen()),
                    );
                  } else {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ProductListScreen(
                          categoryTitle: cat.nama,
                          categoryId: cat.id,
                        ),
                      ),
                    );
                  }
                },
                borderRadius: BorderRadius.circular(10),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    cat != null
                        ? buildCategoryIcon(cat, size: 46, emojiSize: 20)
                        : Container(
                            width: 46,
                            height: 46,
                            decoration: BoxDecoration(
                              color: AppColors.primarySoft,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Center(
                              child: Text(
                                '•••',
                                style: TextStyle(fontSize: 20),
                              ),
                            ),
                          ),
                    const SizedBox(height: 6),
                    Text(
                      cat?.nama ?? 'Lainnya',
                      textAlign: TextAlign.center,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: AppColors.text,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
