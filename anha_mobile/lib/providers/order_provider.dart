import 'package:flutter/material.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/order_model.dart';

class OrderProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<OrderModel> _orders = [];
  OrderModel? _currentOrder;
  bool _isLoading = false;
  String? _errorMessage;

  List<OrderModel> get orders => _orders;
  OrderModel? get currentOrder => _currentOrder;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  List<OrderModel> get activeOrders => _orders
      .where((o) => o.status.toLowerCase() != 'completed' && o.status.toLowerCase() != 'cancelled' && o.status.toLowerCase() != 'selesai' && o.status.toLowerCase() != 'dibatalkan')
      .toList();
  List<OrderModel> get completedOrders => _orders
      .where((o) => o.status.toLowerCase() == 'completed' || o.status.toLowerCase() == 'selesai')
      .toList();
  List<OrderModel> get cancelledOrders => _orders
      .where((o) => o.status.toLowerCase() == 'cancelled' || o.status.toLowerCase() == 'dibatalkan')
      .toList();

  void clear() {
    _orders = [];
    _currentOrder = null;
    _isLoading = false;
    _errorMessage = null;
    notifyListeners();
  }

  Future<void> fetchOrders() async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _api.get(ApiEndpoints.orders);
      final resData = response.data;
      if (resData != null) {
        final list = (resData is Map ? resData['data'] : resData) as List?;
        if (list != null) {
          _orders = list.map((e) => OrderModel.fromJson(e)).toList();
        } else {
          _orders = [];
        }
      } else {
        _orders = [];
      }
    } catch (_) {
      // Keep empty if API error or user has no orders. Do NOT inject dummy orders.
      _orders = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<OrderModel?> fetchOrderDetail(int orderId) async {
    try {
      final response = await _api.get('${ApiEndpoints.orders}/$orderId');
      final resData = response.data;
      if (resData != null) {
        final orderData = resData is Map ? (resData['data'] ?? resData) : null;
        if (orderData != null && orderData is Map<String, dynamic>) {
          final loadedOrder = OrderModel.fromJson(orderData);
          final idx = _orders.indexWhere((o) => o.id == orderId);
          if (idx >= 0) {
            _orders[idx] = loadedOrder;
          } else {
            _orders.insert(0, loadedOrder);
          }
          if (_currentOrder?.id == orderId || _currentOrder == null) {
            _currentOrder = loadedOrder;
          }
          notifyListeners();
          return loadedOrder;
        }
      }
    } catch (_) {}
    return null;
  }

  Future<OrderModel?> placeOrder({
    required int addressId,
    String? voucherCode,
    String? note,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _api.post(
        ApiEndpoints.checkout,
        data: {
          'alamat': addressId,
          'alamat_id': addressId,
          if (voucherCode != null && voucherCode.isNotEmpty) 'voucher_code': voucherCode,
          if (note != null && note.isNotEmpty) 'notes': note,
        },
      );

      final resData = response.data;
      if (response.statusCode == 200 || response.statusCode == 201 || (resData is Map && resData['success'] == true)) {
        final orderData = resData['data']?['order'] ?? resData['data'];
        final placed = orderData != null
            ? OrderModel.fromJson(orderData)
            : OrderModel(
                id: DateTime.now().millisecondsSinceEpoch % 100000,
                invoice: '#ORD-${DateTime.now().year}${DateTime.now().month.toString().padLeft(2, '0')}${DateTime.now().day.toString().padLeft(2, '0')}',
                total: 270000,
                status: 'pending',
                estimatedPoints: 6,
              );

        _orders.insert(0, placed);
        _currentOrder = placed;
        _isLoading = false;
        notifyListeners();
        return placed;
      } else {
        _errorMessage = resData['message'] ?? 'Gagal membuat pesanan.';
        _isLoading = false;
        notifyListeners();
        return null;
      }
    } catch (e) {
      _errorMessage = 'Gagal memproses checkout. Pastikan alamat dan stok mencukupi.';
      _isLoading = false;
      notifyListeners();
      return null;
    }
  }

  Future<bool> confirmReceived(int orderId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      final response = await _api.patch('${ApiEndpoints.orders}/$orderId${ApiEndpoints.confirmReceived}');
      if (response.statusCode == 200 || response.data['success'] == true) {
        final idx = _orders.indexWhere((o) => o.id == orderId);
        if (idx >= 0) {
          _orders[idx] = OrderModel(
            id: _orders[idx].id,
            invoice: _orders[idx].invoice,
            total: _orders[idx].total,
            status: 'completed',
            estimatedPoints: _orders[idx].estimatedPoints,
            alamatPengiriman: _orders[idx].alamatPengiriman,
            items: _orders[idx].items,
            createdAt: _orders[idx].createdAt,
            driverName: _orders[idx].driverName,
            driverPhone: _orders[idx].driverPhone,
          );
        }
        if (_currentOrder?.id == orderId) {
          _currentOrder = _orders.where((o) => o.id == orderId).firstOrNull;
        }
        _isLoading = false;
        notifyListeners();
        return true;
      }
    } catch (_) {}
    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<bool> cancelOrder(int orderId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      final response = await _api.patch('${ApiEndpoints.orders}/$orderId${ApiEndpoints.cancelOrder}');
      if (response.statusCode == 200 || response.data['success'] == true) {
        final idx = _orders.indexWhere((o) => o.id == orderId);
        if (idx >= 0) {
          _orders[idx] = OrderModel(
            id: _orders[idx].id,
            invoice: _orders[idx].invoice,
            total: _orders[idx].total,
            status: 'cancelled',
            estimatedPoints: 0,
            alamatPengiriman: _orders[idx].alamatPengiriman,
            items: _orders[idx].items,
            createdAt: _orders[idx].createdAt,
            driverName: _orders[idx].driverName,
            driverPhone: _orders[idx].driverPhone,
          );
        }
        if (_currentOrder?.id == orderId) {
          _currentOrder = _orders.where((o) => o.id == orderId).firstOrNull;
        }
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.data['message'] ?? 'Pesanan tidak dapat dibatalkan.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Pesanan tidak dapat dibatalkan karena sedang diproses.';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }
}
