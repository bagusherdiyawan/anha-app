class UserModel {
  final int id;
  final String name;
  final String? email;
  final String? phone;
  final String? address;
  final String role; // 'ritel', 'grosir', 'admin'
  final String? subscriptionNumber;
  final String? companyName;
  final String? photoUrl;
  final int points;
  final bool isActive;
  final int totalOrders;

  UserModel({
    required this.id,
    required this.name,
    this.email,
    this.phone,
    this.address,
    required this.role,
    this.subscriptionNumber,
    this.companyName,
    this.photoUrl,
    this.points = 0,
    this.isActive = true,
    this.totalOrders = 0,
  });

  bool get isB2B => role.toLowerCase() == 'grosir';
  bool get isAdmin => role.toLowerCase() == 'admin';
  bool get isB2C => role.toLowerCase() == 'ritel' || (!isB2B && !isAdmin);

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final rawOrders = json['total_orders'] ?? json['totalOrders'] ?? 0;
    final int parsedOrders = rawOrders is int ? rawOrders : int.tryParse(rawOrders.toString()) ?? 0;

    return UserModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? '',
      email: json['email'],
      phone: json['phone'],
      address: json['address'],
      role: json['type'] ?? json['role'] ?? 'ritel',
      subscriptionNumber: json['subscription_number'] ?? json['kode_grosir'],
      companyName: json['company_name'],
      photoUrl: json['photo_url'],
      points: json['points'] is int ? json['points'] : int.tryParse(json['points']?.toString() ?? '0') ?? 0,
      isActive: json['is_active'] == true || json['is_active'] == 1,
      totalOrders: parsedOrders,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'address': address,
      'role': role,
      'subscription_number': subscriptionNumber,
      'company_name': companyName,
      'photo_url': photoUrl,
      'points': points,
      'is_active': isActive,
      'total_orders': totalOrders,
    };
  }
}
