import 'package:flutter_test/flutter_test.dart';
import 'package:anha_mobile/main.dart';

void main() {
  testWidgets('AnhaElectricalApp smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const AnhaElectricalApp());
    expect(find.byType(AnhaElectricalApp), findsOneWidget);
  });
}
