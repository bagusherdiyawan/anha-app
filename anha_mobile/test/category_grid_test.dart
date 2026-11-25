import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:anha_mobile/models/category_model.dart';
import 'package:anha_mobile/views/home/widgets/category_grid.dart';

void main() {
  testWidgets('CategoryGrid renders correctly without TypeError when categories populated', (tester) async {
    final cats = [
      CategoryModel(id: 1, nama: 'Stop Kontak'),
      CategoryModel(id: 2, nama: 'Kabel Listrik'),
      CategoryModel(id: 3, nama: 'MCB 16A'),
      CategoryModel(id: 4, nama: 'Lampu LED'),
    ];

    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SingleChildScrollView(
            child: Column(
              children: [
                CategoryGrid(categories: cats),
              ],
            ),
          ),
        ),
      ),
    );

    expect(find.text('Kategori Terpopuler'), findsOneWidget);
    expect(find.text('Stop Kontak'), findsOneWidget);
    expect(find.text('Kabel Listrik'), findsOneWidget);
    expect(find.text('Lainnya'), findsOneWidget);
  });

  testWidgets('CategoryGrid renders Image widget when category has foto URL', (tester) async {
    final cats = [
      CategoryModel(
        id: 1,
        nama: 'Lampu LED',
        foto: 'https://api.anhagroup.com/storage/photos_kategori/lampu.webp',
      ),
    ];

    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: CategoryGrid(categories: cats),
        ),
      ),
    );

    expect(find.byType(Image), findsOneWidget);
  });
}

