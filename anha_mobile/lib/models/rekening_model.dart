import '../core/constants/api_endpoints.dart';

class RekeningModel {
  final int id;
  final String nama;
  final String nomor;
  final String type;
  final String? foto;
  final String? keterangan;

  RekeningModel({
    required this.id,
    required this.nama,
    required this.nomor,
    required this.type,
    this.foto,
    this.keterangan,
  });

  String? get photoUrl {
    if (foto == null || foto!.isEmpty) return null;
    if (foto!.startsWith('http')) return foto;
    return '${ApiEndpoints.storageUrl}$foto';
  }

  factory RekeningModel.fromJson(Map<String, dynamic> json) {
    return RekeningModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      nama: json['nama']?.toString() ?? '',
      nomor: json['nomor']?.toString() ?? '',
      type: json['type']?.toString() ?? 'bank',
      foto: json['foto']?.toString(),
      keterangan: json['keterangan']?.toString(),
    );
  }
}
