import 'product_model.dart';

class CartItemModel {
  final int id;
  final int barangId;
  int qty;
  final int harga;
  final int subtotal;
  final ProductModel? barang;

  CartItemModel({
    required this.id,
    required this.barangId,
    required this.qty,
    required this.harga,
    required this.subtotal,
    this.barang,
  });

  factory CartItemModel.fromJson(Map<String, dynamic> json) {
    ProductModel? prod;
    if (json['barang'] is Map<String, dynamic>) {
      prod = ProductModel.fromJson(json['barang']);
    } else if (json['product_name'] != null) {
      prod = ProductModel(
        id: json['product_id'] is int ? json['product_id'] : int.tryParse(json['product_id']?.toString() ?? '0') ?? 0,
        namaBarang: json['product_name']?.toString() ?? '',
        kodeBarang: json['product_code']?.toString(),
        satuan: ProductModel.parseSatuan(json),
        harga: json['unit_price'] is int ? json['unit_price'] : int.tryParse(json['unit_price']?.toString() ?? '0') ?? 0,
        stock: json['stock'] is int ? json['stock'] : int.tryParse(json['stock']?.toString() ?? '0') ?? 0,
        isAvailable: json['is_available'] == true || json['is_available'] == 1,
      );
    }

    final rawQty = json['quantity'] ?? json['qty'] ?? 1;
    final int parsedQty = rawQty is int ? rawQty : int.tryParse(rawQty.toString()) ?? 1;

    final rawHarga = json['unit_price'] ?? json['harga'] ?? prod?.harga ?? 0;
    final int parsedHarga = rawHarga is int ? rawHarga : int.tryParse(rawHarga.toString()) ?? 0;

    final rawSubtotal = json['subtotal'] ?? (parsedQty * parsedHarga);
    final int parsedSubtotal = rawSubtotal is int ? rawSubtotal : int.tryParse(rawSubtotal.toString()) ?? (parsedQty * parsedHarga);

    final rawBarangId = json['product_id'] ?? json['barang_id'] ?? prod?.id ?? 0;
    final int parsedBarangId = rawBarangId is int ? rawBarangId : int.tryParse(rawBarangId.toString()) ?? 0;

    return CartItemModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      barangId: parsedBarangId,
      qty: parsedQty,
      harga: parsedHarga,
      subtotal: parsedSubtotal,
      barang: prod,
    );
  }
}
