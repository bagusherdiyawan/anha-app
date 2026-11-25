class AddressModel {
  final int id;
  final String label;
  final String recipientName;
  final String phone;
  final String address;
  final String? district;
  final String? city;
  final String? province;
  final String? postalCode;
  final String? notes;
  final bool isDefault;
  final double? latitude;
  final double? longitude;

  AddressModel({
    required this.id,
    required this.label,
    required this.recipientName,
    required this.phone,
    required this.address,
    this.district,
    this.city,
    this.province,
    this.postalCode,
    this.notes,
    this.isDefault = false,
    this.latitude,
    this.longitude,
  });

  String get fullAddress {
    final parts = [
      address,
      if (district != null && district!.isNotEmpty) district,
      if (city != null && city!.isNotEmpty) city,
      if (province != null && province!.isNotEmpty) province,
      if (postalCode != null && postalCode!.isNotEmpty) postalCode,
    ];
    return parts.join(', ');
  }

  factory AddressModel.fromJson(Map<String, dynamic> json) {
    double? parseCoord(dynamic val) {
      if (val == null) return null;
      if (val is num) return val.toDouble();
      return double.tryParse(val.toString());
    }

    return AddressModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      label: json['label'] ?? 'Rumah',
      recipientName: json['recipient_name'] ?? '',
      phone: json['phone'] ?? '',
      address: json['address'] ?? '',
      district: json['district'],
      city: json['city'],
      province: json['province'],
      postalCode: json['postal_code']?.toString(),
      notes: json['notes'],
      isDefault: json['default'] == true || json['default'] == 1,
      latitude: parseCoord(json['latitude']),
      longitude: parseCoord(json['longitude']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'label': label,
      'recipient_name': recipientName,
      'phone': phone,
      'address': address,
      'district': district,
      'city': city,
      'province': province,
      'postal_code': postalCode,
      'notes': notes,
      'default': isDefault ? 1 : 0,
      'latitude': latitude,
      'longitude': longitude,
    };
  }
}
