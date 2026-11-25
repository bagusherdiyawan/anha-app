import 'address_model.dart';
import 'product_model.dart';

class OrderItemModel {
  final int id;
  final int barangId;
  final int qty;
  final int harga;
  final ProductModel? barang;

  OrderItemModel({
    required this.id,
    required this.barangId,
    required this.qty,
    required this.harga,
    this.barang,
  });

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    final rawQty = json['qty'] ?? 1;
    final int parsedQty = rawQty is int ? rawQty : int.tryParse(rawQty.toString()) ?? 1;

    final rawHarga = json['harga'] ?? 0;
    final int parsedHarga = rawHarga is int ? rawHarga : int.tryParse(rawHarga.toString()) ?? 0;

    bool hasPositivePrice(dynamic value) =>
        (double.tryParse(value?.toString() ?? '') ?? 0) > 0;

    ProductModel? prod;
    if (json['barang'] is Map) {
      final barangMap = Map<String, dynamic>.from(json['barang'] as Map);
      final hasBarangPrice = hasPositivePrice(barangMap['price']) ||
          hasPositivePrice(barangMap['retail_price']) ||
          hasPositivePrice(barangMap['harga']) ||
          (barangMap['harga_ritel'] is Map && hasPositivePrice(barangMap['harga_ritel']['harga']));
      if (!hasBarangPrice && parsedHarga > 0) {
        barangMap['harga'] = parsedHarga;
      }
      prod = ProductModel.fromJson(barangMap);
    }

    return OrderItemModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      barangId: json['barang_id'] is int ? json['barang_id'] : int.tryParse(json['barang_id'].toString()) ?? 0,
      qty: parsedQty,
      harga: parsedHarga,
      barang: prod,
    );
  }
}

class OrderModel {
  final int id;
  final String invoice;
  final int total;
  final int shippingCost;
  final String status;
  final int estimatedPoints;
  final AddressModel? alamatPengiriman;
  final List<OrderItemModel> items;
  final String? createdAt;
  final String? driverName;
  final String? driverPhone;
  final double? driverLat;
  final double? driverLng;

  OrderModel({
    required this.id,
    required this.invoice,
    required this.total,
    this.shippingCost = 0,
    required this.status,
    this.estimatedPoints = 0,
    this.alamatPengiriman,
    this.items = const [],
    this.createdAt,
    this.driverName,
    this.driverPhone,
    this.driverLat,
    this.driverLng,
  });

  // Wireframe Statuses:
  // 1: Confirmed (Menunggu Konfirmasi)
  // 2: Processing (Diproses)
  // 3: In Delivery (Dikirim / Dalam Pengiriman)
  // 4: COD Payment (Sopir Tiba, Menunggu Pembayaran)
  // 5: Done (Selesai)
  // 6: Cancelled (Dibatalkan)

  int get progressStep {
    switch (status.toLowerCase()) {
      case 'pending':
      case 'confirmed':
        return 1;
      case 'processing':
        return 2;
      case 'delivering':
      case 'shipping':
        return 3;
      case 'cod_payment':
      case 'payment_pending':
        return 4;
      case 'completed':
      case 'selesai':
        return 5;
      default:
        return 1;
    }
  }

  String get statusLabel {
    switch (status.toLowerCase()) {
      case 'pending':
      case 'confirmed':
        return 'Terkonfirmasi';
      case 'processing':
        return 'Diproses';
      case 'delivering':
      case 'shipping':
        return 'Dikirim';
      case 'cod_payment':
      case 'payment_pending':
        return 'COD - Bayar Tunai';
      case 'completed':
      case 'selesai':
        return 'Selesai';
      case 'cancelled':
      case 'dibatalkan':
        return 'Dibatalkan';
      default:
        return status;
    }
  }

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    AddressModel? addr;
    if (json['alamat_pengiriman'] is Map<String, dynamic>) {
      addr = AddressModel.fromJson(json['alamat_pengiriman']);
    }

    List<OrderItemModel> itemList = [];
    if (json['order_items'] is List) {
      itemList = (json['order_items'] as List).map((e) => OrderItemModel.fromJson(e)).toList();
    } else if (json['orderItems'] is List) {
      itemList = (json['orderItems'] as List).map((e) => OrderItemModel.fromJson(e)).toList();
    }

    final rawTotal = json['total'] ?? 0;
    final int parsedTotal = rawTotal is int ? rawTotal : int.tryParse(rawTotal.toString()) ?? 0;

    final rawShipping = json['shipping_cost'] ?? 0;
    final int parsedShipping = rawShipping is num
        ? rawShipping.toInt()
        : (double.tryParse(rawShipping.toString())?.round() ?? 0);

    final rawPoints = json['estimated_points'] ?? json['estimasi_poin'] ?? json['points'] ?? 0;
    final int parsedPoints = rawPoints is int ? rawPoints : int.tryParse(rawPoints.toString()) ?? 0;

    return OrderModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      invoice: json['invoice'] ?? '#ORD-${json['id'] ?? ''}',
      total: parsedTotal,
      shippingCost: parsedShipping,
      status: json['status'] ?? 'pending',
      estimatedPoints: parsedPoints,
      alamatPengiriman: addr,
      items: itemList,
      createdAt: json['created_at']?.toString(),
      driverName: json['driver']?['name'] ?? (json['driver_name'] ?? 'Kurir Toko ANHA'),
      driverPhone: json['driver']?['phone'] ?? json['driver_phone'],
      driverLat: -6.229728, // Default shop / driver coordinate for OSM map
      driverLng: 106.689431,
    );
  }
}
