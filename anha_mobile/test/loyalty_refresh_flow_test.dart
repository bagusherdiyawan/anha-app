import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:anha_mobile/core/constants/api_endpoints.dart';
import 'package:anha_mobile/core/network/api_client.dart';
import 'package:anha_mobile/models/order_model.dart';
import 'package:anha_mobile/models/user_model.dart';
import 'package:anha_mobile/providers/auth_provider.dart';
import 'package:anha_mobile/providers/cart_provider.dart';
import 'package:anha_mobile/providers/loyalty_provider.dart';
import 'package:anha_mobile/providers/notification_provider.dart';
import 'package:anha_mobile/providers/order_provider.dart';
import 'package:anha_mobile/providers/product_provider.dart';
import 'package:anha_mobile/views/home/main_navigation_screen.dart';
import 'package:anha_mobile/views/loyalty/loyalty_screen.dart';
import 'package:anha_mobile/views/orders/order_tracking_screen.dart';

class _FakeLoyaltyApiClient implements ApiClient {
  @override
  Dio dio = Dio();

  int pointsBalance = 52;
  int totalEarned = 52;
  int totalSpent = 0;
  int expired = 0;
  bool failTransactions = false;
  List<Map<String, dynamic>> rewards = [
    {
      'id': 1,
      'type': 'physical_item',
      'name': 'Box MCB',
      'points_required': 40,
      'stock': 5,
      'is_active': true,
    },
  ];
  List<Map<String, dynamic>> transactions = [
    {
      'id': 1,
      'amount': 2,
      'description': 'Poin didapatkan dari pesanan #INV-1794390097745',
      'created_at': '2026-11-11T09:43:10.000000Z',
    },
  ];
  final List<String> callLog = [];

  Response _ok(String path, dynamic data) => Response(
        requestOptions: RequestOptions(path: path),
        statusCode: 200,
        data: data,
      );

  @override
  Future<Response> get(String path, {Map<String, dynamic>? queryParameters, Options? options}) async {
    callLog.add('GET:$path');
    if (path == ApiEndpoints.loyaltySummary) {
      return _ok(path, {
        'data': {
          'points_balance': pointsBalance,
          'total_earned': totalEarned,
          'total_spent': totalSpent,
          'expired': expired,
          'points_value': pointsBalance * 500,
        },
      });
    }
    if (path == ApiEndpoints.loyaltyTransactions) {
      if (failTransactions) {
        throw DioException(requestOptions: RequestOptions(path: path));
      }
      return _ok(path, {'data': transactions});
    }
    if (path == ApiEndpoints.loyaltyRewards) {
      return _ok(path, {'data': rewards});
    }
    return _ok(path, {'data': []});
  }

  @override
  Future<Response> post(String path, {dynamic data, Map<String, dynamic>? queryParameters, Options? options}) async =>
      _ok(path, {'success': true});

  @override
  Future<Response> put(String path, {dynamic data, Map<String, dynamic>? queryParameters, Options? options}) async =>
      _ok(path, {'success': true});

  @override
  Future<Response> patch(String path, {dynamic data, Map<String, dynamic>? queryParameters, Options? options}) async =>
      _ok(path, {'success': true});

  @override
  Future<Response> delete(String path, {dynamic data, Map<String, dynamic>? queryParameters, Options? options}) async =>
      _ok(path, {'success': true});
}

class _StubAuthProvider extends AuthProvider {
  int fetchProfileCalls = 0;

  @override
  UserModel? get currentUser => UserModel(
        id: 1,
        name: 'Harun',
        role: 'ritel',
        phone: '08123456789',
      );

  @override
  Future<void> fetchProfile() async {
    fetchProfileCalls++;
  }

  @override
  Future<void> fetchContactInfo() async {}
}

class _StubProductProvider extends ProductProvider {
  @override
  Future<void> fetchBanners() async {}

  @override
  Future<void> fetchCategories() async {}

  @override
  Future<void> fetchAllCategories() async {}

  @override
  Future<void> fetchLandingProducts() async {}
}

class _StubCartProvider extends CartProvider {
  @override
  Future<void> fetchCart() async {}
}

class _StubNotificationProvider extends NotificationProvider {
  int fetchUnreadCalls = 0;

  @override
  Future<void> fetchUnreadCount() async {
    fetchUnreadCalls++;
  }
}

class _StubOrderProvider extends OrderProvider {
  int fetchOrdersCalls = 0;
  OrderModel? orderToReturn;
  bool confirmSuccess = true;
  int confirmCalls = 0;

  @override
  List<OrderModel> get orders => orderToReturn != null ? [orderToReturn!] : [];

  @override
  OrderModel? get currentOrder => orderToReturn;

  @override
  Future<void> fetchOrders() async {
    fetchOrdersCalls++;
  }

  @override
  Future<OrderModel?> fetchOrderDetail(int orderId) async => orderToReturn;

  @override
  Future<bool> confirmReceived(int orderId) async {
    confirmCalls++;
    return confirmSuccess;
  }
}

void main() {
  group('Loyalty Refresh & Summary Tests', () {
    test('LoyaltyProvider fetches updated points silently on subsequent refreshes and preserves transactions on error', () async {
      final api = _FakeLoyaltyApiClient();
      final provider = LoyaltyProvider(apiClient: api);

      await provider.fetchAllLoyaltyData();
      expect(provider.isLoading, isFalse);
      expect(provider.currentBalance, 52);
      expect(provider.summary?.totalEarned, 52);
      expect(provider.transactions.length, 1);

      // Simulate admin marking another order completed (+10 points)
      api.pointsBalance = 62;
      api.totalEarned = 62;
      api.transactions.insert(0, {
        'id': 2,
        'amount': 10,
        'description': 'Poin didapatkan dari pesanan #INV-1794462920073',
        'created_at': '2026-11-12T12:00:00.000000Z',
      });

      final loadingStates = <bool>[];
      provider.addListener(() => loadingStates.add(provider.isLoading));
      await provider.fetchAllLoyaltyData(silent: true);

      expect(loadingStates.any((v) => v == true), isFalse);
      expect(provider.currentBalance, 62);
      expect(provider.summary?.totalEarned, 62);
      expect(provider.transactions.length, 2);

      // Transient network error preserves existing transactions instead of wiping them
      api.failTransactions = true;
      await provider.fetchTransactions();
      expect(provider.transactions.length, 2);
    });

    testWidgets('LoyaltyScreen renders server stats (no hardcoded 450/120) and supports pull-to-refresh', (tester) async {
      final api = _FakeLoyaltyApiClient();
      final loyalty = LoyaltyProvider(apiClient: api);

      await tester.pumpWidget(
        ChangeNotifierProvider<LoyaltyProvider>.value(
          value: loyalty,
          child: const MaterialApp(home: LoyaltyScreen()),
        ),
      );
      await tester.pumpAndSettle();

      expect(find.text('52 Poin'), findsOneWidget);
      expect(find.text('+52'), findsOneWidget);
      expect(find.text('-0'), findsOneWidget);
      expect(find.text('+450'), findsNothing);
      expect(find.text('-120'), findsNothing);

      // Admin awards 8 points while user is already viewing LoyaltyScreen
      api.pointsBalance = 60;
      api.totalEarned = 60;
      api.transactions.insert(0, {
        'id': 9,
        'amount': 8,
        'description': 'Poin didapatkan dari pesanan #INV-NEW',
        'created_at': '2026-11-12T13:00:00.000000Z',
      });

      await tester.fling(find.byType(SingleChildScrollView), const Offset(0, 300), 1000);
      await tester.pumpAndSettle();

      expect(find.text('60 Poin'), findsOneWidget);
      expect(find.text('+60'), findsOneWidget);
      expect(find.text('Poin didapatkan dari pesanan #INV-NEW'), findsOneWidget);
    });

    testWidgets('MainNavigationScreen refreshes Loyalty points on tab switch and AppLifecycleState.resumed without re-login', (tester) async {
      final api = _FakeLoyaltyApiClient()
        ..pointsBalance = 10
        ..totalEarned = 10
        ..transactions = [];
      final loyalty = LoyaltyProvider(apiClient: api);
      final auth = _StubAuthProvider();
      final orders = _StubOrderProvider();
      final notifs = _StubNotificationProvider();

      await tester.pumpWidget(
        MultiProvider(
          providers: [
            ChangeNotifierProvider<AuthProvider>.value(value: auth),
            ChangeNotifierProvider<ProductProvider>(create: (_) => _StubProductProvider()),
            ChangeNotifierProvider<CartProvider>(create: (_) => _StubCartProvider()),
            ChangeNotifierProvider<OrderProvider>.value(value: orders),
            ChangeNotifierProvider<LoyaltyProvider>.value(value: loyalty),
            ChangeNotifierProvider<NotificationProvider>.value(value: notifs),
          ],
          child: const MaterialApp(home: MainNavigationScreen(initialIndex: 0)),
        ),
      );
      await tester.pumpAndSettle();

      // IndexedStack already ran LoyaltyScreen.initState once with 10 Poin
      expect(loyalty.currentBalance, 10);

      // Admin marks order Selesai in background while user stayed on Home
      api.pointsBalance = 25;
      api.totalEarned = 25;
      api.transactions = [
        {
          'id': 77,
          'amount': 15,
          'description': 'Poin didapatkan dari pesanan #INV-SELESAI-1',
          'created_at': '2026-11-12T13:05:00.000000Z',
        },
      ];

      // User taps LOYALTY tab -> auto-refreshes without logout/login
      await tester.tap(find.text('LOYALTY'));
      await tester.pumpAndSettle();

      expect(find.text('25 Poin'), findsOneWidget);
      expect(find.text('Poin didapatkan dari pesanan #INV-SELESAI-1'), findsOneWidget);

      // Another order completes while Loyalty tab stays active & user resumes app
      api.pointsBalance = 33;
      api.totalEarned = 33;
      tester.binding.handleAppLifecycleStateChanged(AppLifecycleState.resumed);
      await tester.pumpAndSettle();
      expect(find.text('33 Poin'), findsOneWidget);

      // Tapping ORDERS & PROFILE tabs refreshes those respective datasets too
      final initialOrderCalls = orders.fetchOrdersCalls;
      await tester.tap(find.text('ORDERS'));
      await tester.pumpAndSettle();
      expect(orders.fetchOrdersCalls, greaterThan(initialOrderCalls));

      final initialProfileCalls = auth.fetchProfileCalls;
      await tester.tap(find.text('PROFILE'));
      await tester.pumpAndSettle();
      expect(auth.fetchProfileCalls, greaterThan(initialProfileCalls));
    });

    testWidgets('OrderTrackingScreen confirmReceived refreshes LoyaltyProvider, OrderProvider, and AuthProvider', (tester) async {
      final api = _FakeLoyaltyApiClient();
      final loyalty = LoyaltyProvider(apiClient: api);
      final auth = _StubAuthProvider();
      final orders = _StubOrderProvider()
        ..orderToReturn = OrderModel(
          id: 55,
          invoice: 'INV-55',
          total: 200000,
          status: 'delivering',
          estimatedPoints: 5,
        );

      await tester.pumpWidget(
        MultiProvider(
          providers: [
            ChangeNotifierProvider<AuthProvider>.value(value: auth),
            ChangeNotifierProvider<OrderProvider>.value(value: orders),
            ChangeNotifierProvider<LoyaltyProvider>.value(value: loyalty),
          ],
          child: const MaterialApp(
            home: OrderTrackingScreen(orderId: 55, invoiceNumber: 'INV-55'),
          ),
        ),
      );
      await tester.pumpAndSettle();

      api.pointsBalance = 57;
      api.totalEarned = 57;

      await tester.tap(find.text('Konfirmasi Terima Barang'));
      await tester.pumpAndSettle();

      expect(orders.confirmCalls, 1);
      expect(orders.fetchOrdersCalls, greaterThanOrEqualTo(1));
      expect(auth.fetchProfileCalls, greaterThanOrEqualTo(1));
      expect(loyalty.currentBalance, 57);
    });

    testWidgets('LoyaltyScreen preview shows server rewards (no dummy Voucher PLN/LED) and matches RewardCatalogScreen filters', (tester) async {
      final api = _FakeLoyaltyApiClient()
        ..pointsBalance = 120
        ..totalEarned = 120
        ..rewards = [
          {
            'id': 10,
            'type': 'physical_item',
            'name': 'Box MCB',
            'points_required': 40,
            'stock': 5,
            'is_active': true,
          },
          {
            'id': 11,
            'type': 'voucher_discount',
            'name': 'Voucher Belanja 10rb',
            'points_required': 80,
            'stock': -1,
            'is_active': true,
          },
        ];
      final loyalty = LoyaltyProvider(apiClient: api);

      await tester.pumpWidget(
        ChangeNotifierProvider<LoyaltyProvider>.value(
          value: loyalty,
          child: const MaterialApp(home: LoyaltyScreen()),
        ),
      );
      await tester.pumpAndSettle();

      // Server rewards appear instead of hardcoded dummy cards
      expect(find.text('Voucher PLN Rp 5rb'), findsNothing);
      expect(find.text('LED Bulb 9W Philips'), findsNothing);
      expect(find.text('Box MCB'), findsOneWidget);
      expect(find.text('Voucher Belanja 10rb'), findsOneWidget);

      // Tap Lihat Semua -> navigates to RewardCatalogScreen with identical items & working backend type filters
      await tester.tap(find.text('Lihat Semua'));
      await tester.pumpAndSettle();

      expect(find.text('Box MCB'), findsOneWidget);
      expect(find.text('Voucher Belanja 10rb'), findsOneWidget);
      expect(find.text('Tersedia'), findsOneWidget); // unlimited stock (-1)

      await tester.tap(find.text('Hadiah Barang'));
      await tester.pumpAndSettle();
      expect(find.text('Box MCB'), findsOneWidget);
      expect(find.text('Voucher Belanja 10rb'), findsNothing);

      await tester.tap(find.text('Voucher'));
      await tester.pumpAndSettle();
      expect(find.text('Box MCB'), findsNothing);
      expect(find.text('Voucher Belanja 10rb'), findsOneWidget);
    });
  });
}
