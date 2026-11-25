import 'package:flutter/material.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/loyalty_model.dart';

class LoyaltyProvider extends ChangeNotifier {
  final ApiClient _api;

  LoyaltyProvider({ApiClient? apiClient}) : _api = apiClient ?? ApiClient();

  LoyaltySummaryModel? _summary;
  List<RewardModel> _rewards = [];
  List<RedemptionModel> _redemptions = [];
  List<PointTransactionModel> _transactions = [];
  List<VoucherModel> _vouchers = [];

  bool _isLoading = false;
  Future<void>? _inFlightFetchAll;
  String? _errorMessage;
  final Set<String> _failedSections = {};

  /// Pesan jika ada bagian data loyalty yang gagal dimuat dari server.
  String? get loadError => _failedSections.isEmpty
      ? null
      : 'Gagal memuat ${_failedSections.join(', ')}. Tarik layar ke bawah untuk mencoba lagi.';

  LoyaltySummaryModel? get summary => _summary;
  List<RewardModel> get rewards => _rewards;
  List<RedemptionModel> get redemptions => _redemptions;
  List<PointTransactionModel> get transactions => _transactions;
  List<VoucherModel> get vouchers => _vouchers;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  int get currentBalance => _summary?.balance ?? 0;

  Future<void> fetchAllLoyaltyData({bool silent = false}) {
    if (_inFlightFetchAll != null) {
      return _inFlightFetchAll!;
    }

    final future = _fetchAllLoyaltyDataInternal(silent: silent);
    _inFlightFetchAll = future;
    future.whenComplete(() {
      if (identical(_inFlightFetchAll, future)) {
        _inFlightFetchAll = null;
      }
    });
    return future;
  }

  Future<void> _fetchAllLoyaltyDataInternal({required bool silent}) async {
    if (!silent) {
      _isLoading = true;
      notifyListeners();
    }

    _failedSections.clear();
    await Future.wait([
      fetchSummary(),
      fetchRewards(),
      fetchRedemptions(),
      fetchTransactions(),
      fetchVouchers(),
    ]);

    if (!silent) {
      _isLoading = false;
    }
    notifyListeners();
  }

  List? _extractList(dynamic resData) {
    if (resData == null) return null;
    if (resData is List) return resData;
    if (resData is Map) {
      final d = resData['data'];
      if (d is List) return d;
      if (d is Map && d['data'] is List) return d['data'];
    }
    return null;
  }

  Future<List<T>?> _fetchTypedList<T>(
    String endpoint,
    T Function(Map<String, dynamic>) fromJson,
    String section,
  ) async {
    try {
      final response = await _api.get(endpoint);
      final list = _extractList(response.data);
      if (list == null) return [];
      return list
          .whereType<Map>()
          .map((e) => fromJson(Map<String, dynamic>.from(e)))
          .toList();
    } catch (_) {
      _failedSections.add(section);
      return null;
    }
  }

  Future<void> fetchSummary() async {
    try {
      final response = await _api.get(ApiEndpoints.loyaltySummary);
      final resData = response.data;
      if (resData != null) {
        final d = (resData is Map && resData['data'] is Map) ? resData['data'] : resData;
        if (d is Map) {
          _summary = LoyaltySummaryModel.fromJson(Map<String, dynamic>.from(d));
          notifyListeners();
        }
      }
    } catch (_) {
      // Jangan memalsukan saldo 0; tandai gagal agar UI menampilkan peringatan.
      _failedSections.add('saldo poin');
      notifyListeners();
    }
  }

  Future<void> fetchRewards() async {
    final items = await _fetchTypedList(ApiEndpoints.loyaltyRewards, RewardModel.fromJson, 'katalog reward');
    if (items != null) _rewards = items;
    notifyListeners();
  }

  Future<void> fetchRedemptions() async {
    final items = await _fetchTypedList(ApiEndpoints.loyaltyRedemptions, RedemptionModel.fromJson, 'riwayat penukaran');
    if (items != null) _redemptions = items;
    notifyListeners();
  }

  Future<void> fetchTransactions() async {
    final items = await _fetchTypedList(ApiEndpoints.loyaltyTransactions, PointTransactionModel.fromJson, 'riwayat poin');
    if (items != null) _transactions = items;
    notifyListeners();
  }

  Future<void> fetchVouchers() async {
    final items = await _fetchTypedList(ApiEndpoints.loyaltyVouchers, VoucherModel.fromJson, 'voucher');
    if (items != null) _vouchers = items;
    notifyListeners();
  }

  Future<RedemptionModel?> redeemReward(RewardModel reward) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _api.post('${ApiEndpoints.loyaltyRedeem}/${reward.id}');
      if (response.data['success'] == true) {
        final redemption = RedemptionModel.fromJson(response.data['data']);
        _redemptions.insert(0, redemption);
        if (_summary != null) {
          final newBal = (_summary!.balance - reward.pointsRequired).clamp(0, 99999999);
          _summary = LoyaltySummaryModel(
            balance: newBal,
            totalEarned: _summary!.totalEarned,
            totalSpent: _summary!.totalSpent + reward.pointsRequired,
            expired: _summary!.expired,
            rupiahValue: newBal * 500,
          );
        }
        await Future.wait([
          fetchSummary(),
          fetchTransactions(),
          fetchVouchers(),
        ]);
        _isLoading = false;
        notifyListeners();
        return redemption;
      } else {
        _errorMessage = response.data['message'] ?? 'Poin tidak mencukupi.';
        _isLoading = false;
        notifyListeners();
        return null;
      }
    } catch (e) {
      _errorMessage = 'Gagal memproses penukaran poin. Silakan coba kembali.';
      _isLoading = false;
      notifyListeners();
      return null;
    }
  }

  void clear() {
    _summary = null;
    _rewards = [];
    _redemptions = [];
    _transactions = [];
    _vouchers = [];
    _failedSections.clear();
    notifyListeners();
  }
}
