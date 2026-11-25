import '../core/constants/api_endpoints.dart';

class CategoryModel {
  final int id;
  final String? kode;
  final String nama;
  final String? deskripsi;
  final String? foto;
  final bool? landing;
  final bool? statusActive;

  CategoryModel({
    required this.id,
    this.kode,
    required this.nama,
    this.deskripsi,
    this.foto,
    this.landing,
    this.statusActive,
  });

  /// Returns a normalized network URL for the category image, or null when unset.
  String? get fullPhotoUrl => ApiEndpoints.normalizeStorageUrl(foto);

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    final rawFoto = json['foto'] ?? json['icon'] ?? json['foto_url'];
    return CategoryModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      kode: json['kode']?.toString(),
      nama: json['nama'] ?? json['name'] ?? json['nama_kategori'] ?? '',
      deskripsi: (json['deskripsi'] ?? json['description'])?.toString(),
      foto: rawFoto?.toString(),
      landing: json['landing'] == true || json['landing'] == 1,
      statusActive: json['status_active'] == true || json['status_active'] == 1,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'kode': kode,
      'nama': nama,
      'deskripsi': deskripsi,
      'foto': foto,
      'landing': landing,
      'status_active': statusActive,
    };
  }
}
