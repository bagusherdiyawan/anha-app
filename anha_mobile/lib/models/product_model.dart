import '../core/constants/api_endpoints.dart';

class WholesaleTier {
  final int minQty;
  final int price;

  WholesaleTier({required this.minQty, required this.price});

  factory WholesaleTier.fromJson(Map<String, dynamic> json) {
    return WholesaleTier(
      minQty: json['min_qty'] is int ? json['min_qty'] : int.tryParse(json['min_qty']?.toString() ?? '1') ?? 1,
      price: json['price'] is int ? json['price'] : int.tryParse(json['price']?.toString() ?? '0') ?? 0,
    );
  }
}

class ProductModel {
  final int id;
  final String namaBarang;
  final String? kodeBarang;
  final String? barcode;
  final String? satuan;
  final String? deskripsi;
  final double rating;
  final int stock;
  final bool isAvailable;
  final int harga; // Based on active role or retail
  final int? hargaRitel;
  final int? hargaGrosir;
  final String? foto;
  final List<String> fotos;
  final String? categoryName;
  final List<WholesaleTier> wholesaleTiers;

  ProductModel({
    required this.id,
    required this.namaBarang,
    this.kodeBarang,
    this.barcode,
    this.satuan,
    this.deskripsi,
    this.rating = 5.0,
    this.stock = 0,
    this.isAvailable = true,
    required this.harga,
    this.hargaRitel,
    this.hargaGrosir,
    this.foto,
    this.fotos = const [],
    this.categoryName,
    this.wholesaleTiers = const [],
  });

  bool get inStock => stock > 0 && isAvailable;

  /// Returns all deduplicated, normalized network URLs for the product gallery.
  List<String> get fullPhotoUrls {
    final seen = <String>{};
    final urls = <String>[];
    for (final candidate in [if (foto != null) foto!, ...fotos]) {
      final normalized = ApiEndpoints.normalizeStorageUrl(candidate);
      if (normalized != null && seen.add(normalized)) {
        urls.add(normalized);
      }
    }
    return urls;
  }

  String? get fullPhotoUrl => fullPhotoUrls.isNotEmpty ? fullPhotoUrls.first : null;

  /// Merges fresh detail payload (e.g. multi-photo gallery, stock) into a list item.
  ProductModel mergeWithDetail(ProductModel detail) {
    final useDetailPhotos = detail.fullPhotoUrls.isNotEmpty;
    return ProductModel(
      id: detail.id > 0 ? detail.id : id,
      namaBarang: detail.namaBarang.isNotEmpty ? detail.namaBarang : namaBarang,
      kodeBarang: detail.kodeBarang ?? kodeBarang,
      barcode: detail.barcode ?? barcode,
      satuan: (detail.satuan != null && detail.satuan!.isNotEmpty) ? detail.satuan : satuan,
      deskripsi: (detail.deskripsi != null && detail.deskripsi!.isNotEmpty) ? detail.deskripsi : deskripsi,
      rating: detail.rating > 0 ? detail.rating : rating,
      stock: detail.stock,
      isAvailable: detail.isAvailable,
      harga: detail.harga > 0 ? detail.harga : harga,
      hargaRitel: detail.hargaRitel ?? hargaRitel,
      hargaGrosir: detail.hargaGrosir ?? hargaGrosir,
      foto: useDetailPhotos ? detail.foto : foto,
      fotos: useDetailPhotos ? detail.fotos : fotos,
      categoryName: (detail.categoryName != null && detail.categoryName!.isNotEmpty)
          ? detail.categoryName
          : categoryName,
      wholesaleTiers: detail.wholesaleTiers.isNotEmpty ? detail.wholesaleTiers : wholesaleTiers,
    );
  }

  static String? _extractPhotoUrl(dynamic entry) {
    if (entry == null) return null;
    if (entry is Map) {
      for (final key in ['foto_url', 'url_foto_barang', 'foto', 'image_url', 'url']) {
        final raw = entry[key]?.toString().trim();
        if (raw != null && raw.isNotEmpty && raw.toLowerCase() != 'null') {
          return raw;
        }
      }
      return null;
    }
    final raw = entry.toString().trim();
    if (raw.isEmpty || raw.toLowerCase() == 'null' || raw.startsWith('{')) {
      return null;
    }
    return raw;
  }

  static void _collectPhotos(dynamic source, List<String> target, Set<String> seenNormalized) {
    if (source == null) return;
    if (source is List) {
      for (final item in source) {
        _collectPhotos(item, target, seenNormalized);
      }
      return;
    }
    final extracted = _extractPhotoUrl(source);
    final normalized = ApiEndpoints.normalizeStorageUrl(extracted);
    if (extracted != null && normalized != null && seenNormalized.add(normalized)) {
      target.add(extracted);
    }
  }

  /// Resolves unit name and rejects raw numeric foreign-key IDs (e.g. "1").
  static String parseSatuan(Map<String, dynamic> json) {
    final unitRel = json['satuan_barang'] ?? json['satuanBarang'];
    final candidates = [
      json['satuan_nama'],
      if (unitRel is Map) unitRel['nama'],
      json['satuan'],
    ];

    for (final candidate in candidates) {
      final raw = candidate?.toString().trim();
      if (raw == null || raw.isEmpty || raw.toLowerCase() == 'null') continue;
      if (RegExp(r'^\d+$').hasMatch(raw)) continue;
      return raw;
    }
    return 'pcs';
  }

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    final List<String> photoList = [];
    final Set<String> seenNormalized = {};
    _collectPhotos(json['primary_image_url'], photoList, seenNormalized);
    _collectPhotos(json['foto'], photoList, seenNormalized);
    _collectPhotos(json['images'], photoList, seenNormalized);
    _collectPhotos(json['fotos'], photoList, seenNormalized);

    // Default wholesale tiers if not provided by backend
    final rawHarga = json['price'] ?? json['retail_price'] ?? json['harga'] ?? json['harga_ritel']?['harga'] ?? 0;
    final int parsedHarga = rawHarga is int ? rawHarga : int.tryParse(rawHarga.toString()) ?? 0;

    List<WholesaleTier> tiers = [];
    if (json['wholesale_tiers'] is List) {
      tiers = (json['wholesale_tiers'] as List).map((e) => WholesaleTier.fromJson(e)).toList();
    }

    final rawRating = json['quality_rating'] ?? json['rating'] ?? 5.0;
    final double parsedRating = rawRating is num ? rawRating.toDouble() : (double.tryParse(rawRating.toString()) ?? 5.0);

    final catName = json['category'] is Map
        ? (json['category']['name'] ?? json['category']['nama'])
        : (json['kategori'] is Map ? (json['kategori']['name'] ?? json['kategori']['nama']) : null);

    return ProductModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      namaBarang: json['name'] ?? json['nama_barang'] ?? '',
      kodeBarang: json['code'] ?? json['kode_barang'],
      barcode: json['barcode'],
      satuan: parseSatuan(json),
      deskripsi: json['description'] ?? json['deskripsi'],
      rating: parsedRating,
      stock: json['stock'] is int ? json['stock'] : int.tryParse(json['stock']?.toString() ?? '0') ?? 0,
      isAvailable: json['is_available'] == true || json['is_available'] == 1,
      harga: parsedHarga,
      hargaRitel: json['retail_price'] is int ? json['retail_price'] : (json['harga_ritel'] is int ? json['harga_ritel'] : parsedHarga),
      hargaGrosir: json['wholesale_price'] is int ? json['wholesale_price'] : (json['harga_grosir'] is int ? json['harga_grosir'] : null),
      foto: photoList.isNotEmpty ? photoList.first : null,
      fotos: photoList,
      categoryName: catName?.toString(),
      wholesaleTiers: tiers,
    );
  }
}
