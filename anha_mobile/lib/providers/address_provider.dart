import 'package:flutter/material.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/address_model.dart';

class AddressProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<AddressModel> _addresses = [];
  AddressModel? _selectedAddress;
  bool _isLoading = false;
  String? _errorMessage;

  List<AddressModel> get addresses => _addresses;
  AddressModel? get selectedAddress =>
      _selectedAddress ??
      _addresses.where((a) => a.isDefault).firstOrNull ??
      _addresses.firstOrNull;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchAddresses() async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _api.get(ApiEndpoints.addresses);
      final resData = response.data;
      if (resData != null) {
        List? list;
        if (resData is Map && resData['data'] is List) {
          list = resData['data'];
        } else if (resData is List) {
          list = resData;
        }

        if (list != null) {
          _addresses = list.map((e) => AddressModel.fromJson(e)).toList();
        } else {
          _addresses = [];
        }
      } else {
        _addresses = [];
      }
    } catch (_) {
      // Keep empty if API error or user has no addresses
      _addresses = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void selectAddress(AddressModel address) {
    _selectedAddress = address;
    notifyListeners();
  }

  Future<bool> addAddress(AddressModel address) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _api.post(
        ApiEndpoints.addresses,
        data: address.toJson(),
      );
      if (response.statusCode == 200 || response.statusCode == 201 || response.data != null) {
        await fetchAddresses();
        return true;
      }
    } catch (_) {}

    // Add locally for development if offline
    _addresses.add(address);
    _isLoading = false;
    notifyListeners();
    return true;
  }

  Future<bool> updateAddress(int id, AddressModel address) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _api.put(
        '${ApiEndpoints.addresses}/$id',
        data: address.toJson(),
      );
      if (response.statusCode == 200 || response.data != null) {
        await fetchAddresses();
        return true;
      }
    } catch (_) {}

    // Update locally for fallback
    final idx = _addresses.indexWhere((a) => a.id == id);
    if (idx >= 0) {
      _addresses[idx] = address;
    }
    _isLoading = false;
    notifyListeners();
    return true;
  }

  Future<void> setDefaultAddress(int id) async {
    try {
      await _api.patch('${ApiEndpoints.setDefaultAddress}/$id');
    } catch (_) {}

    for (var i = 0; i < _addresses.length; i++) {
      final a = _addresses[i];
      _addresses[i] = AddressModel(
        id: a.id,
        label: a.label,
        recipientName: a.recipientName,
        phone: a.phone,
        address: a.address,
        district: a.district,
        city: a.city,
        province: a.province,
        postalCode: a.postalCode,
        notes: a.notes,
        isDefault: a.id == id,
        latitude: a.latitude,
        longitude: a.longitude,
      );
    }
    notifyListeners();
  }

  Future<void> deleteAddress(int id) async {
    _addresses.removeWhere((a) => a.id == id);
    if (_selectedAddress?.id == id) {
      _selectedAddress = _addresses.firstOrNull;
    }
    notifyListeners();

    try {
      await _api.delete('${ApiEndpoints.addresses}/$id');
    } catch (_) {}
  }

  void clear() {
    _addresses = [];
    _selectedAddress = null;
    notifyListeners();
  }
}
