import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:anha_mobile/models/user_model.dart';
import 'package:anha_mobile/providers/auth_provider.dart';
import 'package:anha_mobile/providers/loyalty_provider.dart';
import 'package:anha_mobile/providers/order_provider.dart';
import 'package:anha_mobile/views/profile/profile_screen.dart';

class _StubAuthProvider extends AuthProvider {
  @override
  UserModel? get currentUser => UserModel(
        id: 2,
        name: 'Harun',
        role: 'ritel',
        email: '0002',
        phone: '08123456789',
      );
}

class _StubLoyaltyProvider extends LoyaltyProvider {
  @override
  int get currentBalance => 45;
}

class _StubOrderProvider extends OrderProvider {}

void main() {
  testWidgets(
    'ProfileScreen keeps Edit Profil & Alamat Pengiriman without Ubah Password menu',
    (tester) async {
      await tester.pumpWidget(
        MultiProvider(
          providers: [
            ChangeNotifierProvider<AuthProvider>(create: (_) => _StubAuthProvider()),
            ChangeNotifierProvider<LoyaltyProvider>(create: (_) => _StubLoyaltyProvider()),
            ChangeNotifierProvider<OrderProvider>(create: (_) => _StubOrderProvider()),
          ],
          child: const MaterialApp(
            home: ProfileScreen(),
          ),
        ),
      );

      await tester.pump();

      expect(find.text('Edit Profil'), findsOneWidget);
      expect(find.text('Alamat Pengiriman (Map OSM)'), findsOneWidget);
      expect(find.text('Voucher Belanja Saya'), findsOneWidget);
      expect(find.text('Ubah Password / PIN Akun'), findsNothing);
      expect(find.textContaining('Ubah Password'), findsNothing);
    },
  );
}

