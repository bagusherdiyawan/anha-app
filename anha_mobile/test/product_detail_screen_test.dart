import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:anha_mobile/models/product_model.dart';
import 'package:anha_mobile/providers/cart_provider.dart';
import 'package:anha_mobile/providers/product_provider.dart';
import 'package:anha_mobile/views/browse/product_detail_screen.dart';

class _FakeProductProvider extends ProductProvider {
  final ProductModel? detailResponse;

  _FakeProductProvider(this.detailResponse);

  @override
  Future<ProductModel?> fetchProductDetail(int productId) async {
    return detailResponse;
  }
}

void main() {
  testWidgets(
    'ProductDetailScreen loads full gallery slider and resolves unit label cleanly',
    (tester) async {
      final listProduct = ProductModel.fromJson({
        'id': 24,
        'code': '24',
        'name': 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
        'satuan': '1',
        'harga': 495000,
        'stock': 8,
        'is_available': 1,
        'primary_image_url': 'photos_barang/eterna1.webp',
      });

      final detailProduct = ProductModel.fromJson({
        'id': 24,
        'code': '24',
        'name': 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
        'satuan': '1',
        'satuan_nama': 'Roll',
        'harga': 495000,
        'stock': 8,
        'is_available': true,
        'images': [
          {'foto_url': 'https://api.anhagroup.com/storage/photos_barang/eterna1.webp'},
          {'foto_url': 'https://api.anhagroup.com/storage/photos_barang/eterna2.webp'},
        ],
      });

      await tester.pumpWidget(
        MultiProvider(
          providers: [
            ChangeNotifierProvider(create: (_) => CartProvider()),
            ChangeNotifierProvider<ProductProvider>(
              create: (_) => _FakeProductProvider(detailProduct),
            ),
          ],
          child: MaterialApp(
            home: ProductDetailScreen(product: listProduct),
          ),
        ),
      );

      // Flush post-frame detail fetch
      await tester.pumpAndSettle();

      // Unit name resolved properly and stock never renders trailing ID '1'
      expect(find.text(' / Roll'), findsOneWidget);
      expect(find.text('Sisa stok: 8 Roll'), findsOneWidget);
      expect(find.textContaining('8 1'), findsNothing);

      // Slider rendered with 2 photos and counter updates on swipe
      expect(find.byType(PageView), findsOneWidget);
      expect(find.text('1 / 2'), findsOneWidget);

      await tester.fling(find.byType(PageView), const Offset(-400, 0), 1000);
      await tester.pumpAndSettle();

      expect(find.text('2 / 2'), findsOneWidget);
    },
  );
}

