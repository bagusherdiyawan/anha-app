import '../core/constants/api_endpoints.dart';

class LoyaltySummaryModel {
  final int balance;
  final int totalEarned;
  final int totalSpent;
  final int expired;
  final int rupiahValue;

  LoyaltySummaryModel({
    required this.balance,
    required this.totalEarned,
    required this.totalSpent,
    this.expired = 0,
    required this.rupiahValue,
  });

  factory LoyaltySummaryModel.fromJson(Map<String, dynamic> json) {
    final rawBal = json['points_balance'] ?? json['balance'] ?? 0;
    final bal = rawBal is int ? rawBal : int.tryParse(rawBal.toString()) ?? 0;

    final rawVal = json['points_value'] ?? json['rupiah_value'] ?? (bal * 500);
    final val = rawVal is int ? rawVal : int.tryParse(rawVal.toString()) ?? (bal * 500);

    return LoyaltySummaryModel(
      balance: bal,
      totalEarned: json['total_earned'] is int ? json['total_earned'] : int.tryParse(json['total_earned']?.toString() ?? '0') ?? 0,
      totalSpent: json['total_spent'] is int ? json['total_spent'] : int.tryParse(json['total_spent']?.toString() ?? '0') ?? 0,
      expired: json['expired'] is int ? json['expired'] : int.tryParse(json['expired']?.toString() ?? '0') ?? 0,
      rupiahValue: val,
    );
  }
}

class RewardModel {
  final int id;
  final String name;
  final String? type; // 'physical', 'voucher', 'discount'
  final String? description;
  final String? image;
  final int pointsRequired;
  final int stock;
  final bool isActive;

  RewardModel({
    required this.id,
    required this.name,
    this.type,
    this.description,
    this.image,
    required this.pointsRequired,
    required this.stock,
    this.isActive = true,
  });

  bool get isUnlimitedStock => stock == -1;

  bool get inStock => isActive && (isUnlimitedStock || stock > 0);

  String get normalizedCategory {
    switch (type?.toLowerCase()) {
      case 'voucher_discount':
      case 'voucher':
        return 'voucher';
      case 'direct_discount':
      case 'discount':
        return 'discount';
      default:
        return 'physical';
    }
  }

  factory RewardModel.fromJson(Map<String, dynamic> json) {
    final rawImage = json['image_url'] ?? json['image'];
    final rawActive = json['is_active'];
    final isActive = rawActive == null
        ? true
        : (rawActive == true ||
            rawActive == 1 ||
            rawActive.toString().toLowerCase() == 'true' ||
            rawActive.toString() == '1');

    return RewardModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString() ?? 'physical_item',
      description: json['description']?.toString(),
      image: ApiEndpoints.normalizeStorageUrl(rawImage?.toString()),
      pointsRequired: json['points_required'] is int ? json['points_required'] : int.tryParse(json['points_required']?.toString() ?? '0') ?? 0,
      stock: json['stock'] is int ? json['stock'] : int.tryParse(json['stock']?.toString() ?? '0') ?? 0,
      isActive: isActive,
    );
  }
}

class RedemptionModel {
  final int id;
  final int pointsSpent;
  final String claimCode;
  final String status; // 'pending' (Belum Diambil) / 'claimed' (Sudah Diambil)
  final String? claimedAt;
  final String? createdAt;
  final RewardModel? reward;

  RedemptionModel({
    required this.id,
    required this.pointsSpent,
    required this.claimCode,
    required this.status,
    this.claimedAt,
    this.createdAt,
    this.reward,
  });

  bool get isClaimed => status.toLowerCase() == 'claimed' || status.toLowerCase() == 'sudah_diambil';

  String get statusDisplay => isClaimed ? 'Sudah Diambil' : 'Belum Diambil';

  factory RedemptionModel.fromJson(Map<String, dynamic> json) {
    RewardModel? rew;
    if (json['reward'] is Map<String, dynamic>) {
      rew = RewardModel.fromJson(json['reward']);
    }

    return RedemptionModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      pointsSpent: json['points_spent'] is int ? json['points_spent'] : int.tryParse(json['points_spent']?.toString() ?? '0') ?? 0,
      claimCode: json['claim_code'] ?? 'ANHA-${json['id'] ?? ''}',
      status: json['status'] ?? 'pending',
      claimedAt: json['claimed_at']?.toString(),
      createdAt: json['created_at']?.toString(),
      reward: rew,
    );
  }
}

class PointTransactionModel {
  final int id;
  final int amount; // positive for earn, negative for spend
  final String description;
  final String? createdAt;

  PointTransactionModel({
    required this.id,
    required this.amount,
    required this.description,
    this.createdAt,
  });

  factory PointTransactionModel.fromJson(Map<String, dynamic> json) {
    return PointTransactionModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      amount: json['amount'] is int ? json['amount'] : int.tryParse(json['amount']?.toString() ?? '0') ?? 0,
      description: json['description'] ?? 'Transaksi Poin',
      createdAt: json['created_at']?.toString(),
    );
  }
}

class VoucherModel {
  final int id;
  final String code;
  final double discountValue;
  final String discountType; // 'fixed' or 'percent'
  final double? maxDiscount;
  final double minPurchase;
  final bool isUsed;
  final String? expiresAt;

  VoucherModel({
    required this.id,
    required this.code,
    required this.discountValue,
    required this.discountType,
    this.maxDiscount,
    this.minPurchase = 0,
    this.isUsed = false,
    this.expiresAt,
  });

  factory VoucherModel.fromJson(Map<String, dynamic> json) {
    return VoucherModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      code: json['code']?.toString() ?? '',
      discountValue: (json['discount_value'] is num ? json['discount_value'] : double.tryParse(json['discount_value']?.toString() ?? '0') ?? 0).toDouble(),
      discountType: json['discount_type']?.toString() ?? 'fixed',
      maxDiscount: json['max_discount'] != null ? double.tryParse(json['max_discount'].toString()) : null,
      minPurchase: (json['min_purchase'] is num ? json['min_purchase'] : double.tryParse(json['min_purchase']?.toString() ?? '0') ?? 0).toDouble(),
      isUsed: json['is_used'] == true || json['is_used'] == 1,
      expiresAt: json['expires_at']?.toString(),
    );
  }
}

