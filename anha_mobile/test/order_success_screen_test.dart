import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:anha_mobile/views/cart/order_success_screen.dart';

void main() {
  testWidgets('OrderSuccessScreen displays correct earnedPoints and invoiceNumber', (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: OrderSuccessScreen(
          invoiceNumber: 'INV-1792996518651',
          earnedPoints: 12,
          total: 493000,
        ),
      ),
    );

    expect(find.text('INV-1792996518651'), findsOneWidget);
    expect(find.text('+12 Poin Listrik'), findsOneWidget);
    expect(find.text('Pesanan Berhasil!'), findsOneWidget);
  });
}

