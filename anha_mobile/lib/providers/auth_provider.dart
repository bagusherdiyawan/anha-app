import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../core/storage/local_storage.dart';
import '../models/user_model.dart';

class AuthProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  UserModel? _currentUser;
  bool _isLoading = false;
  String? _errorMessage;
  String _whatsappNumber = '6285169189914';
  String _whatsappTemplate = 'Halo ANHA Electrical, saya butuh bantuan terkait aplikasi / kode akses.';

  UserModel? get currentUser => _currentUser;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isLoggedIn => _currentUser != null;
  String get whatsappNumber => _whatsappNumber;

  Future<void> initAuth() async {
    final token = await LocalStorage.getToken();
    final userMap = await LocalStorage.getUser();

    if (token != null && userMap != null) {
      _currentUser = UserModel.fromJson(userMap);
      notifyListeners();
      // Fetch fresh profile in background
      fetchProfile();
    }
    fetchContactInfo();
  }

  Future<bool> loginWithCode(String loginCode) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _api.post(
        ApiEndpoints.login,
        data: {'login_code': loginCode.trim()},
      );

      final data = response.data;
      if (data != null && (data['success'] == true || response.statusCode == 200)) {
        final token = data['data']?['token'] ?? data['token'];
        final userObj = data['data']?['user'] ?? data['user'];

        if (token != null) {
          await LocalStorage.saveToken(token.toString());
        }

        if (userObj != null) {
          _currentUser = UserModel.fromJson(userObj);
          await LocalStorage.saveUser(userObj);
        }

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = data['message'] ?? 'Kode login tidak valid atau akun tidak aktif.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Gagal terhubung ke server. Periksa kode atau koneksi internet Anda.';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchProfile() async {
    try {
      final response = await _api.get(ApiEndpoints.profile);
      if (response.data != null) {
        final userObj = response.data['data'] ?? response.data;
        if (userObj is Map<String, dynamic>) {
          _currentUser = UserModel.fromJson(userObj);
          await LocalStorage.saveUser(userObj);
          notifyListeners();
        }
      }
    } catch (_) {}
  }

  Future<bool> updateProfile({
    required String name,
    required String phone,
    String? address,
  }) async {
    if (_currentUser == null) return false;

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final payload = {
        'name': name.trim(),
        'phone': phone.trim(),
        if (address != null && address.isNotEmpty) 'address': address.trim(),
      };

      final response = await _api.put(
        '${ApiEndpoints.profile}/${_currentUser!.id}',
        data: payload,
      );

      if (response.statusCode == 200 || response.data['data'] != null) {
        final updatedData = response.data['data'] ?? response.data;
        if (updatedData is Map<String, dynamic>) {
          _currentUser = UserModel.fromJson(updatedData);
          await LocalStorage.saveUser(updatedData);
        } else {
          // Update local instance if response didn't echo full user
          _currentUser = UserModel(
            id: _currentUser!.id,
            name: name.trim(),
            phone: phone.trim(),
            email: _currentUser!.email,
            role: _currentUser!.role,
            subscriptionNumber: _currentUser!.subscriptionNumber,
            companyName: _currentUser!.companyName,
            photoUrl: _currentUser!.photoUrl,
            points: _currentUser!.points,
            isActive: _currentUser!.isActive,
            totalOrders: _currentUser!.totalOrders,
          );
        }
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response.data['message'] ?? 'Gagal memperbarui profil.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan saat menyimpan profil ke server.';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updatePhoto(String filePath) async {
    if (_currentUser == null) return false;

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final fileName = filePath.split(RegExp(r'[\\/]')).last;
      final file = await MultipartFile.fromFile(filePath, filename: fileName);
      final formData = FormData.fromMap({
        'photo_url': file,
        '_method': 'PUT',
      });

      final response = await _api.post(
        '${ApiEndpoints.updatePhoto}/${_currentUser!.id}',
        data: formData,
      );

      final resData = response.data;
      if (response.statusCode == 200 || (resData is Map && (resData['success'] == true || resData['data'] != null))) {
        final photoUrl = resData is Map
            ? (resData['data']?['photo_url'] ?? resData['photo_url'] ?? resData['data']?['full_photo_url'])
            : null;
        if (photoUrl != null) {
          _currentUser = UserModel(
            id: _currentUser!.id,
            name: _currentUser!.name,
            phone: _currentUser!.phone,
            email: _currentUser!.email,
            role: _currentUser!.role,
            subscriptionNumber: _currentUser!.subscriptionNumber,
            companyName: _currentUser!.companyName,
            photoUrl: photoUrl.toString(),
            points: _currentUser!.points,
            isActive: _currentUser!.isActive,
            totalOrders: _currentUser!.totalOrders,
          );
          await LocalStorage.saveUser(_currentUser!.toJson());
          notifyListeners();
        }
        await fetchProfile();
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = resData is Map ? resData['message'] : 'Gagal memperbarui foto profil.';
      }
    } catch (e) {
      _errorMessage = 'Gagal mengunggah foto ke server. Pastikan ukuran file gambar di bawah 5MB.';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<void> fetchContactInfo() async {
    try {
      final response = await _api.get(ApiEndpoints.contactInfo);
      if (response.data != null && response.data['data'] != null) {
        final data = response.data['data'];
        if (data is Map) {
          _whatsappNumber = data['whatsapp_number'] ?? _whatsappNumber;
          _whatsappTemplate = data['whatsapp_template'] ?? _whatsappTemplate;
        }
      }
    } catch (_) {}
  }

  Future<void> launchWhatsApp({String? customMessage}) async {
    final msg = Uri.encodeComponent(customMessage ?? _whatsappTemplate);
    final url = Uri.parse('https://wa.me/$_whatsappNumber?text=$msg');
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    }
  }

  Future<void> logout() async {
    try {
      await _api.post(ApiEndpoints.logout);
    } catch (_) {}
    await LocalStorage.clearAuth();
    _currentUser = null;
    notifyListeners();
  }
}
