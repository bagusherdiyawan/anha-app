import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/feedback_model.dart';

class FeedbackProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<FeedbackModel> _feedbacks = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<FeedbackModel> get feedbacks => _feedbacks;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchFeedbacks() async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _api.get(ApiEndpoints.feedbacks);
      final data = response.data;
      if (data != null) {
        List? list;
        if (data is Map && data['data'] is List) {
          list = data['data'];
        } else if (data is List) {
          list = data;
        }

        if (list != null) {
          _feedbacks = list.map((e) => FeedbackModel.fromJson(e)).toList();
        } else {
          _feedbacks = [];
        }
      } else {
        _feedbacks = [];
      }
    } catch (_) {
      _feedbacks = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> sendFeedback({
    required String categoryKey, // 'product', 'delivery', 'service', 'app', 'other'
    required String content,
    List<String> photoPaths = const [],
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      dynamic postData;
      if (photoPaths.isNotEmpty) {
        final formData = FormData();
        formData.fields.add(MapEntry('category', categoryKey));
        formData.fields.add(MapEntry('content', content));
        for (var p in photoPaths) {
          formData.files.add(MapEntry('photos[]', await MultipartFile.fromFile(p)));
        }
        postData = formData;
      } else {
        postData = {
          'category': categoryKey,
          'content': content,
        };
      }

      final response = await _api.post(
        ApiEndpoints.feedbacks,
        data: postData,
      );

      if (response.statusCode == 200 || response.statusCode == 201 || response.data['success'] == true) {
        await fetchFeedbacks();
        return true;
      }
    } catch (e) {
      _errorMessage = 'Gagal mengirim kritik/saran. Periksa koneksi internet Anda.';
      _isLoading = false;
      notifyListeners();
      return false;
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }


  void clear() {
    _feedbacks = [];
    notifyListeners();
  }
}
