import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/cart_model.dart';
import '../models/order_model.dart';
import '../models/product_model.dart';

class ReorderResult {
  final int addedCount;
  final List<String> errors;

  const ReorderResult({
    required this.addedCount,
    required this.errors,
  });

  bool get hasErrors => errors.isNotEmpty;
}

class CartProvider extends ChangeNotifier {
  CartProvider({ApiClient? apiClient}) : _api = apiClient ?? ApiClient();

  final ApiClient _api;

  List<CartItemModel> _items = [];
  bool _isLoading = false;
  int _pendingMutations = 0;
  final Set<int> _addingProductIds = {};
  Future<void> _operationQueue = Future.value();
  String? _promoCode;
  int _discount = 0;
  bool _shippingEnabled = false;
  int _shippingFlatRate = 0;
  int _minimumPurchase = 0;
  int _serverEstimatedPoints = 0;

  List<CartItemModel> get items => _items;
  bool get isLoading => _isLoading;
  bool get isMutating => _pendingMutations > 0;
  bool get isBusy => _isLoading || _pendingMutations > 0;
  bool isAddingProduct(int productId) => _addingProductIds.contains(productId);
  Future<void> waitForPendingMutations() => _operationQueue;
  int get itemCount => _items.fold(0, (sum, item) => sum + item.qty);
  bool get shippingEnabled => _shippingEnabled;
  int get shippingFlatRate => _shippingFlatRate;
  int get shippingCost => (_shippingEnabled && _shippingFlatRate > 0) ? _shippingFlatRate : 0;
  int get discount => _discount;
  String? get promoCode => _promoCode;
  int get minimumPurchase => _minimumPurchase;
  bool get isEligibleCheckout {
    if (_items.isEmpty) return false;
    if (_minimumPurchase <= 0) return true;
    return subtotal >= _minimumPurchase;
  }

  int get subtotal => _items.fold(0, (sum, item) => sum + (item.harga * item.qty));
  int get grandTotal => (subtotal + shippingCost - _discount).clamp(0, 999999999);
  
  // Server-calculated points or fallback 1 point per 40.000
  int get estimatedPoints => _serverEstimatedPoints > 0 ? _serverEstimatedPoints : (subtotal / 40000).floor();

  Future<T> _enqueueMutation<T>(Future<T> Function() action) {
    _pendingMutations++;
    notifyListeners();

    final completer = Completer<T>();
    _operationQueue = _operationQueue.then((_) async {
      try {
        final result = await action();
        if (!completer.isCompleted) {
          completer.complete(result);
        }
      } catch (e, st) {
        if (!completer.isCompleted) {
          completer.completeError(e, st);
        }
      } finally {
        _pendingMutations = (_pendingMutations - 1).clamp(0, 999999);
        notifyListeners();
      }
    });
    return completer.future;
  }

  Future<void> _fetchCartInternal() async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _api.get(ApiEndpoints.cart);
      final resData = response.data;
      if (resData != null) {
        final data = resData['data'] ?? resData;
        List? list;
        if (data is Map && data['items'] is List) {
          list = data['items'];
        } else if (data is List) {
          list = data;
        }
        if (data is Map) {
          syncShippingConfig(data);
        }
        if (list != null) {
          _items = list.map((e) => CartItemModel.fromJson(e)).toList();
        }
      }
      await fetchCheckoutPre();
    } catch (_) {} finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchCart() => _enqueueMutation(_fetchCartInternal);

  @visibleForTesting
  void syncShippingConfig(Map data) {
    if (data.containsKey('shipping_enabled')) {
      final rawEnabled = data['shipping_enabled'];
      _shippingEnabled = rawEnabled == true ||
          rawEnabled == 1 ||
          rawEnabled?.toString().toLowerCase() == 'true' ||
          rawEnabled?.toString() == '1';
    }
    if (data.containsKey('shipping_flat_rate')) {
      final rawRate = data['shipping_flat_rate'] ?? 0;
      final parsed = rawRate is num ? rawRate.toInt() : (double.tryParse(rawRate.toString())?.round() ?? 0);
      _shippingFlatRate = parsed < 0 ? 0 : parsed;
    } else if (data.containsKey('shipping_cost')) {
      final rawCost = data['shipping_cost'] ?? 0;
      final parsed = rawCost is num ? rawCost.toInt() : (double.tryParse(rawCost.toString())?.round() ?? 0);
      _shippingFlatRate = parsed < 0 ? 0 : parsed;
    }
  }

  List<CartItemModel> _snapshotItems() {
    return _items
        .map(
          (item) => CartItemModel(
            id: item.id,
            barangId: item.barangId,
            qty: item.qty,
            harga: item.harga,
            subtotal: item.subtotal,
            barang: item.barang,
          ),
        )
        .toList();
  }

  void _restoreItems(List<CartItemModel> snapshot) {
    _items = snapshot;
    notifyListeners();
  }

  String _extractApiError(Object error, String fallback) {
    if (error is DioException) {
      final data = error.response?.data;
      if (data is Map) {
        final msg = data['message']?.toString().trim();
        if (msg != null && msg.isNotEmpty) {
          return msg;
        }
        final errors = data['errors'];
        if (errors is Map) {
          for (final value in errors.values) {
            if (value is List && value.isNotEmpty) {
              return value.first.toString();
            }
            final str = value?.toString().trim();
            if (str != null && str.isNotEmpty) {
              return str;
            }
          }
        }
      } else if (data is String && data.trim().isNotEmpty) {
        return data.trim();
      }
    }
    return fallback;
  }

  int? _resolveCartItemId(int cartItemId) {
    if (cartItemId > 0) return cartItemId;
    final idx = _items.indexWhere((i) => i.barangId == -cartItemId);
    return idx >= 0 ? _items[idx].id : null;
  }

  Future<String?> _addToCartInternal(
    ProductModel product, {
    int qty = 1,
    bool syncAfter = true,
  }) async {
    if (qty <= 0) {
      return 'Jumlah pembelian minimal 1.';
    }

    final snapshot = _snapshotItems();
    final index = _items.indexWhere((i) => i.barangId == product.id);
    if (index >= 0) {
      _items[index].qty += qty;
    } else {
      _items.add(
        CartItemModel(
          id: -product.id,
          barangId: product.id,
          qty: qty,
          harga: product.harga,
          subtotal: product.harga * qty,
          barang: product,
        ),
      );
    }
    notifyListeners();

    try {
      await _api.post(
        ApiEndpoints.cart,
        data: {'barang_id': product.id, 'qty': qty},
      );
      if (syncAfter) {
        await _fetchCartInternal();
      }
      return null;
    } catch (e) {
      _restoreItems(snapshot);
      return _extractApiError(
        e,
        'Gagal menambahkan ${product.namaBarang} ke keranjang.',
      );
    }
  }

  Future<String?> addToCart(
    ProductModel product, {
    int qty = 1,
    bool syncAfter = true,
  }) async {
    _addingProductIds.add(product.id);
    notifyListeners();
    try {
      return await _enqueueMutation(
        () => _addToCartInternal(product, qty: qty, syncAfter: syncAfter),
      );
    } finally {
      _addingProductIds.remove(product.id);
      notifyListeners();
    }
  }

  Future<ReorderResult> reorderOrderItems(List<OrderItemModel> items) {
    if (items.isEmpty) {
      return Future.value(
        const ReorderResult(
          addedCount: 0,
          errors: ['Pesanan ini tidak memiliki item untuk dibeli lagi.'],
        ),
      );
    }

    return _enqueueMutation(() async {
      int addedCount = 0;
      final List<String> errors = [];

      for (final item in items) {
        final product = item.barang;
        if (product == null || product.id <= 0) {
          errors.add('Produk dalam pesanan ini sudah tidak tersedia.');
          continue;
        }

        final error = await _addToCartInternal(product, qty: item.qty, syncAfter: false);
        if (error == null) {
          addedCount++;
        } else {
          errors.add('${product.namaBarang}: $error');
        }
      }

      if (addedCount > 0) {
        await _fetchCartInternal();
      }

      return ReorderResult(addedCount: addedCount, errors: errors);
    });
  }

  Future<String?> _updateQtyInternal(int cartItemId, int newQty) async {
    if (newQty <= 0) {
      return _removeFromCartInternal(cartItemId);
    }

    final resolvedId = _resolveCartItemId(cartItemId);
    if (resolvedId == null) {
      return 'Item keranjang tidak ditemukan.';
    }

    final snapshot = _snapshotItems();
    final index = _items.indexWhere((i) => i.id == resolvedId);
    if (index >= 0) {
      _items[index].qty = newQty;
      notifyListeners();
    }

    try {
      await _api.put(
        '${ApiEndpoints.cart}/$resolvedId',
        data: {'qty': newQty},
      );
      await fetchCheckoutPre();
      return null;
    } catch (e) {
      _restoreItems(snapshot);
      if (e is DioException && e.response?.statusCode == 404) {
        await _fetchCartInternal();
      }
      return _extractApiError(e, 'Gagal memperbarui jumlah item.');
    }
  }

  Future<String?> updateQty(int cartItemId, int newQty) =>
      _enqueueMutation(() => _updateQtyInternal(cartItemId, newQty));

  Future<String?> _removeFromCartInternal(int cartItemId) async {
    final resolvedId = _resolveCartItemId(cartItemId);
    if (resolvedId == null) {
      return null;
    }

    final snapshot = _snapshotItems();
    _items.removeWhere((i) => i.id == resolvedId);
    notifyListeners();

    try {
      await _api.delete('${ApiEndpoints.cart}/$resolvedId');
      await fetchCheckoutPre();
      return null;
    } catch (e) {
      _restoreItems(snapshot);
      if (e is DioException && e.response?.statusCode == 404) {
        await _fetchCartInternal();
      }
      return _extractApiError(e, 'Gagal menghapus item dari keranjang.');
    }
  }

  Future<String?> removeFromCart(int cartItemId) =>
      _enqueueMutation(() => _removeFromCartInternal(cartItemId));

  Future<void> fetchCheckoutPre({String? voucherCode}) async {
    final code = voucherCode ?? _promoCode;
    try {
      final response = await _api.get(
        ApiEndpoints.checkoutPre,
        queryParameters: (code != null && code.isNotEmpty) ? {'voucher_code': code} : null,
      );
      final resData = response.data;
      if (resData != null) {
        if (resData is Map) {
          syncShippingConfig(resData);
        }
        final rawMin = resData['minimum_purchase_amount'] ?? 0;
        _minimumPurchase = rawMin is int ? rawMin : int.tryParse(rawMin.toString()) ?? 0;

        if (code != null && code.isNotEmpty) {
          final bool isVoucherValid = resData['voucher_valid'] == true ||
              (resData['voucher_valid'] == null && (resData['discount_amount'] != null && (resData['discount_amount'] as num) > 0));
          final rawDisc = resData['discount_amount'] ?? 0;
          final disc = rawDisc is int ? rawDisc : int.tryParse(rawDisc.toString()) ?? 0;

          if (isVoucherValid && disc > 0) {
            _discount = disc;
          } else {
            _promoCode = null;
            _discount = 0;
          }
        } else {
          _discount = 0;
        }

        final rawPts = resData['estimasi_poin'] ?? resData['estimated_points'] ?? 0;
        _serverEstimatedPoints = rawPts is int ? rawPts : int.tryParse(rawPts.toString()) ?? 0;

        notifyListeners();
      }
    } catch (_) {}
  }

  Future<String?> applyPromo(String code, {int? explicitDiscount}) async {
    final cleanCode = code.trim();
    if (cleanCode.isEmpty) {
      removePromo();
      return 'Kode voucher tidak boleh kosong.';
    }

    try {
      final response = await _api.get(
        ApiEndpoints.checkoutPre,
        queryParameters: {'voucher_code': cleanCode},
      );
      final resData = response.data;
      if (resData != null) {
        if (resData is Map) {
          syncShippingConfig(resData);
        }
        final bool isVoucherValid = resData['voucher_valid'] == true ||
            (resData['voucher_valid'] == null && (resData['discount_amount'] != null && (resData['discount_amount'] as num) > 0));
        final String? voucherError = resData['voucher_error'];
        final rawDisc = resData['discount_amount'] ?? 0;
        final disc = rawDisc is int ? rawDisc : int.tryParse(rawDisc.toString()) ?? 0;

        if (!isVoucherValid || (voucherError != null && voucherError.isNotEmpty) || (disc <= 0 && explicitDiscount == null)) {
          _promoCode = null;
          _discount = 0;
          notifyListeners();
          return voucherError ?? 'Voucher "$cleanCode" tidak valid atau tidak dapat digunakan.';
        }

        _promoCode = cleanCode;
        _discount = disc > 0 ? disc : (explicitDiscount ?? 0);

        final rawMin = resData['minimum_purchase_amount'] ?? 0;
        _minimumPurchase = rawMin is int ? rawMin : int.tryParse(rawMin.toString()) ?? 0;

        final rawPts = resData['estimasi_poin'] ?? resData['estimated_points'] ?? 0;
        _serverEstimatedPoints = rawPts is int ? rawPts : int.tryParse(rawPts.toString()) ?? 0;

        notifyListeners();
        return null;
      } else {
        _promoCode = null;
        _discount = 0;
        notifyListeners();
        return 'Gagal memvalidasi voucher dari server.';
      }
    } catch (e) {
      _promoCode = null;
      _discount = 0;
      notifyListeners();
      return 'Gagal menghubungi server untuk verifikasi voucher.';
    }
  }

  void removePromo() {
    _promoCode = null;
    _discount = 0;
    notifyListeners();
    fetchCheckoutPre();
  }

  void clearCartLocal() {
    _items.clear();
    _discount = 0;
    _promoCode = null;
    _serverEstimatedPoints = 0;
    notifyListeners();
  }
}

