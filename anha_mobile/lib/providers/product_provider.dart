import 'package:flutter/material.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/category_model.dart';
import '../models/product_model.dart';

class ProductProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<String> _banners = [];
  List<CategoryModel> _categories = [];
  List<CategoryModel> _allCategories = [];
  List<ProductModel> _landingProducts = [];
  List<ProductModel> _categoryProducts = [];
  List<ProductModel> _searchResults = [];

  bool _isLoading = false;
  bool _isCategoriesLoading = false;
  String? _errorMessage;

  List<String> get banners => _banners;
  List<CategoryModel> get categories => _categories;
  List<CategoryModel> get allCategories =>
      _allCategories.isNotEmpty ? _allCategories : _categories;
  List<ProductModel> get landingProducts => _landingProducts;
  List<ProductModel> get categoryProducts => _categoryProducts;
  List<ProductModel> get searchResults => _searchResults;
  bool get isLoading => _isLoading;
  bool get isCategoriesLoading => _isCategoriesLoading;
  String? get errorMessage => _errorMessage;

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

  Future<void> fetchBanners() async {
    try {
      final response = await _api.get(ApiEndpoints.banners);
      final list = _extractList(response.data);
      if (list != null && list.isNotEmpty) {
        _banners = list.map((e) {
          if (e is! Map) return '';
          final path = e['image_path']?.toString() ?? e['image_url']?.toString() ?? e['foto']?.toString() ?? '';
          return ApiEndpoints.normalizeStorageUrl(path) ?? '';
        }).where((s) => s.isNotEmpty).toList();
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> fetchCategories() async {
    try {
      final response = await _api.get(ApiEndpoints.categoriesLanding);
      final list = _extractList(response.data);
      if (list != null && list.isNotEmpty) {
        _categories = list.map((e) => CategoryModel.fromJson(e)).toList();
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> fetchAllCategories() async {
    _isCategoriesLoading = true;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.categoriesAll);
      final list = _extractList(response.data);
      if (list != null && list.isNotEmpty) {
        _allCategories = list.map((e) => CategoryModel.fromJson(e)).toList();
      }
    } catch (_) {} finally {
      _isCategoriesLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchLandingProducts() async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.productsLanding);
      final list = _extractList(response.data);
      if (list != null) {
        _landingProducts = list.map((e) => ProductModel.fromJson(e)).toList();
      } else {
        _landingProducts = [];
      }
    } catch (_) {
      _landingProducts = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchProductsByCategory(int categoryId) async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _api.get(
        '${ApiEndpoints.productsByCategory}/$categoryId',
        queryParameters: {'limit': 100, 'per_page': 100},
      );
      final list = _extractList(response.data);
      if (list != null) {
        _categoryProducts = list.map((e) => ProductModel.fromJson(e)).toList();
      } else {
        _categoryProducts = [];
      }
    } catch (_) {
      _categoryProducts = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<ProductModel?> fetchProductDetail(int productId) async {
    try {
      final response = await _api.get('${ApiEndpoints.productDetail}/$productId');
      final raw = response.data;
      if (raw is Map) {
        final data = raw['data'];
        if (data is Map) {
          return ProductModel.fromJson(Map<String, dynamic>.from(data));
        }
        if (raw['id'] != null) {
          return ProductModel.fromJson(Map<String, dynamic>.from(raw));
        }
      }
    } catch (_) {}
    return null;
  }

  Future<void> searchProducts(String query) async {
    if (query.trim().isEmpty) {
      _searchResults = [];
      notifyListeners();
      return;
    }

    _isLoading = true;
    notifyListeners();

    try {
      final response = await _api.get(
        ApiEndpoints.productSearch,
        queryParameters: {'q': query.trim()},
      );
      final list = _extractList(response.data);
      if (list != null) {
        _searchResults = list.map((e) => ProductModel.fromJson(e)).toList();
      } else {
        _searchResults = [];
      }
    } catch (_) {
      _searchResults = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
