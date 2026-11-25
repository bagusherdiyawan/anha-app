import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:anha_mobile/core/constants/api_endpoints.dart';
import 'package:anha_mobile/core/network/api_client.dart';
import 'package:anha_mobile/models/address_model.dart';
import 'package:anha_mobile/models/order_model.dart';
import 'package:anha_mobile/models/product_model.dart';
import 'package:anha_mobile/providers/address_provider.dart';
import 'package:anha_mobile/providers/cart_provider.dart';
import 'package:anha_mobile/providers/loyalty_provider.dart';
import 'package:anha_mobile/providers/order_provider.dart';
import 'package:anha_mobile/views/cart/cart_screen.dart';
import 'package:anha_mobile/views/orders/order_history_screen.dart';

class _MockResponse {
  final dynamic data;
  final int statusCode;

  _MockResponse({this.data, this.statusCode = 200});
}

class _FakeApiClient implements ApiClient {
  @override
  Dio dio = Dio();

  final List<String> callLog = [];
  final Map<String, FutureOr<_MockResponse> Function(dynamic data, Map<String, dynamic>? query)>
      handlers = {};

  Response _toResponse(String path, _MockResponse res) {
    final req = RequestOptions(path: path);
    if (res.statusCode >= 400) {
      throw DioException(
        requestOptions: req,
        response: Response(
          requestOptions: req,
          statusCode: res.statusCode,
          data: res.data,
        ),
        type: DioExceptionType.badResponse,
      );
    }
    return Response(
      requestOptions: req,
      statusCode: res.statusCode,
      data: res.data,
    );
  }

  @override
  Future<Response> get(String path, {Map<String, dynamic>? queryParameters, Options? options}) async {
    callLog.add('GET:$path');
    final handler = handlers['GET:$path'];
    if (handler != null) {
      return _toResponse(path, await handler(null, queryParameters));
    }
    return _toResponse(path, _MockResponse(data: {}));
  }

  @override
  Future<Response> post(String path, {dynamic data, Map<String, dynamic>? queryParameters, Options? options}) async {
    callLog.add('POST:$path');
    final handler = handlers['POST:$path'];
    if (handler != null) {
      return _toResponse(path, await handler(data, queryParameters));
    }
    return _toResponse(path, _MockResponse(data: {'message': 'OK'}));
  }

  @override
  Future<Response> put(String path, {dynamic data, Map<String, dynamic>? queryParameters, Options? options}) async {
    callLog.add('PUT:$path');
    final handler = handlers['PUT:$path'] ?? handlers['PUT:*'];
    if (handler != null) {
      return _toResponse(path, await handler(data, queryParameters));
    }
    return _toResponse(path, _MockResponse(data: {'success': true}));
  }

  @override
  Future<Response> patch(String path, {dynamic data, Map<String, dynamic>? queryParameters, Options? options}) async {
    callLog.add('PATCH:$path');
    return _toResponse(path, _MockResponse(data: {}));
  }

  @override
  Future<Response> delete(String path, {dynamic data, Map<String, dynamic>? queryParameters, Options? options}) async {
    callLog.add('DELETE:$path');
    final handler = handlers['DELETE:$path'] ?? handlers['DELETE:*'];
    if (handler != null) {
      return _toResponse(path, await handler(data, queryParameters));
    }
    return _toResponse(path, _MockResponse(data: {'success': true}));
  }
}

class _FakeOrderProvider extends OrderProvider {
  final List<OrderModel> seededOrders;

  _FakeOrderProvider(this.seededOrders);

  @override
  List<OrderModel> get orders => seededOrders;

  @override
  Future<void> fetchOrders() async {}
}

class _FakeLoyaltyProvider extends LoyaltyProvider {
  @override
  Future<void> fetchVouchers() async {}
}

class _FakeAddressProvider extends AddressProvider {
  final AddressModel? defaultAddr;

  _FakeAddressProvider([this.defaultAddr]);

  @override
  AddressModel? get selectedAddress => defaultAddr;

  @override
  List<AddressModel> get addresses => defaultAddr != null ? [defaultAddr!] : [];

  @override
  Future<void> fetchAddresses() async {}
}

void main() {
  group('CartProvider Reorder & Sync Tests', () {
    test('addToCart syncs server price, product_code, and DB id after POST', () async {
      final fakeApi = _FakeApiClient();
      fakeApi.handlers['GET:${ApiEndpoints.cart}'] = (_, __) => _MockResponse(
            data: {
              'data': {
                'items': [
                  {
                    'id': 99,
                    'product_id': 24,
                    'product_name': 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
                    'product_code': 'KBL-024',
                    'satuan': 'Roll',
                    'unit_price': 495000,
                    'quantity': 1,
                    'subtotal': 495000,
                    'stock': 8,
                    'is_available': true,
                  },
                ],
                'shipping_enabled': true,
                'shipping_flat_rate': 25000,
                'minimum_purchase_amount': 50000,
              },
            },
          );
      fakeApi.handlers['GET:${ApiEndpoints.checkoutPre}'] = (_, __) => _MockResponse(
            data: {
              'minimum_purchase_amount': 50000,
              'shipping_enabled': true,
              'shipping_flat_rate': 25000,
              'estimasi_poin': 12,
            },
          );

      final cart = CartProvider(apiClient: fakeApi);
      final incompleteProduct = ProductModel(
        id: 24,
        namaBarang: 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
        harga: 0,
      );

      final error = await cart.addToCart(incompleteProduct, qty: 1);

      expect(error, isNull);
      expect(cart.items.length, 1);
      expect(cart.items.first.id, 99);
      expect(cart.items.first.harga, 495000);
      expect(cart.items.first.barang?.kodeBarang, 'KBL-024');
      expect(cart.subtotal, 495000);
      expect(cart.isEligibleCheckout, isTrue);
    });

    test('addToCart rolls back optimistic update and returns server error on failure', () async {
      final fakeApi = _FakeApiClient();
      fakeApi.handlers['POST:${ApiEndpoints.cart}'] = (_, __) => _MockResponse(
            statusCode: 400,
            data: {'message': 'Stok tidak mencukupi. Sisa stok: 8'},
          );

      final cart = CartProvider(apiClient: fakeApi);
      final product = ProductModel(id: 24, namaBarang: 'Kabel Eterna', harga: 495000);

      final error = await cart.addToCart(product, qty: 10);

      expect(error, 'Stok tidak mencukupi. Sisa stok: 8');
      expect(cart.items, isEmpty);
      expect(cart.subtotal, 0);
    });

    test('reorderOrderItems awaits all items and calls fetchCart once on success', () async {
      final fakeApi = _FakeApiClient();
      fakeApi.handlers['GET:${ApiEndpoints.cart}'] = (_, __) => _MockResponse(
            data: {
              'data': {
                'items': [
                  {
                    'id': 50,
                    'product_id': 24,
                    'product_name': 'Kabel Eterna',
                    'product_code': 'KBL-024',
                    'satuan': 'Roll',
                    'unit_price': 495000,
                    'quantity': 1,
                    'subtotal': 495000,
                    'stock': 8,
                    'is_available': true,
                  },
                  {
                    'id': 51,
                    'product_id': 25,
                    'product_name': 'MCB 16A',
                    'product_code': 'MCB-16',
                    'satuan': 'pcs',
                    'unit_price': 55000,
                    'quantity': 2,
                    'subtotal': 110000,
                    'stock': 10,
                    'is_available': true,
                  },
                ],
                'minimum_purchase_amount': 50000,
              },
            },
          );
      fakeApi.handlers['GET:${ApiEndpoints.checkoutPre}'] = (_, __) => _MockResponse(
            data: {'minimum_purchase_amount': 50000},
          );

      final cart = CartProvider(apiClient: fakeApi);
      final order = OrderModel.fromJson({
        'id': 1,
        'invoice': 'INV-123',
        'total': 605000,
        'status': 'completed',
        'orderItems': [
          {
            'id': 1,
            'barang_id': 24,
            'qty': 1,
            'harga': 495000,
            'barang': {'id': 24, 'nama_barang': 'Kabel Eterna', 'satuan': 'Roll'},
          },
          {
            'id': 2,
            'barang_id': 25,
            'qty': 2,
            'harga': 55000,
            'barang': {'id': 25, 'nama_barang': 'MCB 16A', 'satuan': 'pcs'},
          },
        ],
      });

      final result = await cart.reorderOrderItems(order.items);

      expect(result.addedCount, 2);
      expect(result.errors, isEmpty);
      expect(fakeApi.callLog.where((e) => e == 'GET:${ApiEndpoints.cart}').length, 1);
      expect(cart.items.length, 2);
      expect(cart.subtotal, 605000);
    });

    test('updateQty rolls back qty on 400 and resyncs on 404', () async {
      final fakeApi = _FakeApiClient();
      fakeApi.handlers['GET:${ApiEndpoints.cart}'] = (_, __) => _MockResponse(
            data: {
              'data': {
                'items': [
                  {
                    'id': 99,
                    'product_id': 24,
                    'product_name': 'Kabel Eterna',
                    'unit_price': 495000,
                    'quantity': 1,
                    'subtotal': 495000,
                    'stock': 8,
                    'is_available': true,
                  },
                ],
              },
            },
          );
      fakeApi.handlers['PUT:*'] = (_, __) => _MockResponse(
            statusCode: 400,
            data: {'message': 'Stok tidak mencukupi. Sisa stok: 8'},
          );

      final cart = CartProvider(apiClient: fakeApi);
      await cart.fetchCart();
      expect(cart.items.first.qty, 1);

      final err400 = await cart.updateQty(99, 12);
      expect(err400, 'Stok tidak mencukupi. Sisa stok: 8');
      expect(cart.items.first.qty, 1);

      fakeApi.handlers['PUT:*'] = (_, __) => _MockResponse(
            statusCode: 404,
            data: {'message': 'Keranjang tidak ditemukan'},
          );
      final callsBefore = fakeApi.callLog.length;
      final err404 = await cart.updateQty(99, 2);
      expect(err404, 'Keranjang tidak ditemukan');
      expect(
        fakeApi.callLog.sublist(callsBefore),
        contains('GET:${ApiEndpoints.cart}'),
      );
    });
  });

  group('OrderHistoryScreen Beli Lagi -> CartScreen Widget Tests', () {
    testWidgets('Tapping Beli Lagi awaits sync and renders real price & code in CartScreen', (tester) async {
      final fakeApi = _FakeApiClient();
      fakeApi.handlers['GET:${ApiEndpoints.cart}'] = (_, __) => _MockResponse(
            data: {
              'data': {
                'items': [
                  {
                    'id': 99,
                    'product_id': 24,
                    'product_name': 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
                    'product_code': 'KBL-024',
                    'satuan': 'Roll',
                    'unit_price': 495000,
                    'quantity': 1,
                    'subtotal': 495000,
                    'stock': 8,
                    'is_available': true,
                  },
                ],
                'shipping_enabled': true,
                'shipping_flat_rate': 25000,
                'minimum_purchase_amount': 50000,
              },
            },
          );
      fakeApi.handlers['GET:${ApiEndpoints.checkoutPre}'] = (_, __) => _MockResponse(
            data: {
              'minimum_purchase_amount': 50000,
              'shipping_enabled': true,
              'shipping_flat_rate': 25000,
              'estimasi_poin': 12,
            },
          );

      final completedOrder = OrderModel.fromJson({
        'id': 88,
        'invoice': 'INV-1794353943726',
        'total': 495000,
        'status': 'completed',
        'orderItems': [
          {
            'id': 1,
            'barang_id': 24,
            'qty': 1,
            'harga': 495000,
            'barang': {
              'id': 24,
              'nama_barang': 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
              'satuan': 'Roll',
            },
          },
        ],
      });

      final cartProvider = CartProvider(apiClient: fakeApi);

      await tester.pumpWidget(
        MultiProvider(
          providers: [
            ChangeNotifierProvider<CartProvider>.value(value: cartProvider),
            ChangeNotifierProvider<OrderProvider>(
              create: (_) => _FakeOrderProvider([completedOrder]),
            ),
            ChangeNotifierProvider<LoyaltyProvider>(
              create: (_) => _FakeLoyaltyProvider(),
            ),
            ChangeNotifierProvider<AddressProvider>(
              create: (_) => _FakeAddressProvider(),
            ),
          ],
          child: const MaterialApp(
            home: OrderHistoryScreen(),
          ),
        ),
      );

      await tester.pumpAndSettle();

      await tester.tap(find.text('Beli Lagi'));
      await tester.pumpAndSettle();

      // Should open CartScreen with synced code, real price, shipping, and eligible checkout
      expect(find.text('Keranjang Belanja'), findsOneWidget);
      expect(find.text('KBL-024'), findsOneWidget);
      expect(find.text('Original Part'), findsNothing);
      expect(find.text('Rp 495.000'), findsWidgets);
      expect(find.text('Rp 25.000'), findsOneWidget);
      expect(find.textContaining('Rp 520.000'), findsWidgets);
      expect(find.text('Rp 0'), findsNothing);
    });

    testWidgets('Tapping Beli Lagi when server rejects stock stays on OrderHistoryScreen with error', (tester) async {
      final fakeApi = _FakeApiClient();
      fakeApi.handlers['POST:${ApiEndpoints.cart}'] = (_, __) => _MockResponse(
            statusCode: 400,
            data: {'message': 'Stok tidak mencukupi. Sisa stok: 0'},
          );

      final completedOrder = OrderModel.fromJson({
        'id': 88,
        'invoice': 'INV-1794353943726',
        'total': 495000,
        'status': 'completed',
        'orderItems': [
          {
            'id': 1,
            'barang_id': 24,
            'qty': 2,
            'harga': 495000,
            'barang': {
              'id': 24,
              'nama_barang': 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
              'satuan': 'Roll',
            },
          },
        ],
      });

      final cartProvider = CartProvider(apiClient: fakeApi);

      await tester.pumpWidget(
        MultiProvider(
          providers: [
            ChangeNotifierProvider<CartProvider>.value(value: cartProvider),
            ChangeNotifierProvider<OrderProvider>(
              create: (_) => _FakeOrderProvider([completedOrder]),
            ),
            ChangeNotifierProvider<LoyaltyProvider>(
              create: (_) => _FakeLoyaltyProvider(),
            ),
            ChangeNotifierProvider<AddressProvider>(
              create: (_) => _FakeAddressProvider(),
            ),
          ],
          child: const MaterialApp(
            home: OrderHistoryScreen(),
          ),
        ),
      );

      await tester.pumpAndSettle();
      await tester.tap(find.text('Beli Lagi'));
      await tester.pumpAndSettle();

      expect(find.text('Keranjang Belanja'), findsNothing);
      expect(find.textContaining('Stok tidak mencukupi. Sisa stok: 0'), findsOneWidget);
      expect(cartProvider.items, isEmpty);
    });
  });

  group('Cart Mutation Serialization & Checkout Sync Tests', () {
    test('Immediate remove with temporary negative ID resolves real DB ID after queued addToCart', () async {
      final fakeApi = _FakeApiClient();
      final postCompleter = Completer<_MockResponse>();
      fakeApi.handlers['POST:${ApiEndpoints.cart}'] = (_, __) => postCompleter.future;
      fakeApi.handlers['GET:${ApiEndpoints.cart}'] = (_, __) => _MockResponse(
            data: {
              'data': {
                'items': [
                  {
                    'id': 13,
                    'product_id': 77,
                    'product_name': 'Pipa SCALA kuning 5/8"',
                    'product_code': 'PIP-77',
                    'satuan': 'btg',
                    'unit_price': 8000,
                    'quantity': 1,
                    'subtotal': 8000,
                    'stock': 20,
                    'is_available': true,
                  },
                ],
              },
            },
          );

      final cart = CartProvider(apiClient: fakeApi);
      final pipa = ProductModel(id: 77, namaBarang: 'Pipa SCALA kuning 5/8"', harga: 8000);

      // Start addToCart without awaiting, then immediately remove with temp negative id (-77)
      final addFuture = cart.addToCart(pipa, qty: 1);
      final removeFuture = cart.removeFromCart(-77);

      expect(cart.isMutating, isTrue);
      await Future<void>.delayed(Duration.zero);
      expect(fakeApi.callLog, ['POST:${ApiEndpoints.cart}']);

      postCompleter.complete(_MockResponse(data: {'message': 'OK'}));
      await Future.wait([addFuture, removeFuture]);

      expect(cart.isMutating, isFalse);
      expect(fakeApi.callLog, [
        'POST:${ApiEndpoints.cart}',
        'GET:${ApiEndpoints.cart}',
        'GET:${ApiEndpoints.checkoutPre}',
        'DELETE:${ApiEndpoints.cart}/13',
        'GET:${ApiEndpoints.checkoutPre}',
      ]);
      expect(cart.items, isEmpty);
    });

    test('removeFromCart rolls back item when server DELETE fails', () async {
      final fakeApi = _FakeApiClient();
      fakeApi.handlers['GET:${ApiEndpoints.cart}'] = (_, __) => _MockResponse(
            data: {
              'data': {
                'items': [
                  {
                    'id': 13,
                    'product_id': 77,
                    'product_name': 'Pipa SCALA kuning 5/8"',
                    'unit_price': 8000,
                    'quantity': 1,
                    'subtotal': 8000,
                    'stock': 20,
                    'is_available': true,
                  },
                ],
              },
            },
          );
      fakeApi.handlers['DELETE:*'] = (_, __) => _MockResponse(
            statusCode: 500,
            data: {'message': 'Gagal menghapus item di server'},
          );

      final cart = CartProvider(apiClient: fakeApi);
      await cart.fetchCart();
      expect(cart.items.length, 1);

      final err = await cart.removeFromCart(13);
      expect(err, 'Gagal menghapus item di server');
      expect(cart.items.length, 1);
      expect(cart.items.first.id, 13);
    });

    testWidgets('CartScreen locks checkout while DELETE is in flight and updates total after sync', (tester) async {
      final fakeApi = _FakeApiClient();
      bool pipaDeleted = false;
      final deleteCompleter = Completer<_MockResponse>();

      fakeApi.handlers['GET:${ApiEndpoints.cart}'] = (_, __) => _MockResponse(
            data: {
              'data': {
                'items': [
                  {
                    'id': 12,
                    'product_id': 76,
                    'product_name': 'Kabel Audio 2x50 25m Pioline',
                    'product_code': 'KBL-76',
                    'satuan': 'roll',
                    'unit_price': 62000,
                    'quantity': 1,
                    'subtotal': 62000,
                    'stock': 10,
                    'is_available': true,
                  },
                  if (!pipaDeleted)
                    {
                      'id': 13,
                      'product_id': 77,
                      'product_name': 'Pipa SCALA kuning 5/8"',
                      'product_code': 'PIP-77',
                      'satuan': 'btg',
                      'unit_price': 8000,
                      'quantity': 1,
                      'subtotal': 8000,
                      'stock': 20,
                      'is_available': true,
                    },
                ],
                'minimum_purchase_amount': 50000,
                'shipping_enabled': false,
                'shipping_flat_rate': 0,
              },
            },
          );
      fakeApi.handlers['GET:${ApiEndpoints.checkoutPre}'] = (_, __) => _MockResponse(
            data: {'minimum_purchase_amount': 50000},
          );
      fakeApi.handlers['DELETE:${ApiEndpoints.cart}/13'] = (_, __) {
        pipaDeleted = true;
        return deleteCompleter.future;
      };

      final cartProvider = CartProvider(apiClient: fakeApi);
      final testAddress = AddressModel(
        id: 1,
        label: 'Rumah',
        recipientName: 'Budi',
        phone: '08123',
        address: 'Jl. Test',
        district: 'Test',
        city: 'Tangerang',
        province: 'Banten',
        postalCode: '15810',
      );

      await tester.pumpWidget(
        MultiProvider(
          providers: [
            ChangeNotifierProvider<CartProvider>.value(value: cartProvider),
            ChangeNotifierProvider<OrderProvider>(
              create: (_) => _FakeOrderProvider(const []),
            ),
            ChangeNotifierProvider<LoyaltyProvider>(
              create: (_) => _FakeLoyaltyProvider(),
            ),
            ChangeNotifierProvider<AddressProvider>(
              create: (_) => _FakeAddressProvider(testAddress),
            ),
          ],
          child: const MaterialApp(home: CartScreen()),
        ),
      );

      await tester.pumpAndSettle();

      // Initially both Kabel (62k) + Pipa (8k) = 70k
      expect(find.text('Bayar COD — Rp 70.000 ›'), findsOneWidget);

      // Tap '-' on Pipa (second remove icon in list)
      await tester.tap(find.byIcon(Icons.remove).last);
      await tester.pump();

      // While DELETE is in flight, checkout CTA must be locked with sync status
      expect(find.text('Menyinkronkan Keranjang...'), findsOneWidget);
      expect(find.text('Pipa SCALA kuning 5/8"'), findsNothing);

      // Complete backend DELETE
      deleteCompleter.complete(_MockResponse(data: {'success': true}));
      await tester.pumpAndSettle();

      expect(find.text('Bayar COD — Rp 62.000 ›'), findsOneWidget);
      expect(fakeApi.callLog, contains('DELETE:${ApiEndpoints.cart}/13'));
    });
  });
}
