import 'package:flutter/material.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/notification_model.dart';

class NotificationProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<NotificationModel> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = false;
  String? _errorMessage;

  List<NotificationModel> get notifications => _notifications;
  int get unreadCount => _unreadCount;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchNotifications() async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _api.get(ApiEndpoints.notifications);
      final resData = response.data;
      if (resData != null) {
        List? list;
        if (resData is Map && resData['data'] != null) {
          final d = resData['data'];
          if (d is Map && d['data'] is List) {
            // Paginated Laravel response: data.data
            list = d['data'];
          } else if (d is List) {
            list = d;
          }
        } else if (resData is List) {
          list = resData;
        }

        if (list != null) {
          _notifications = list.map((e) => NotificationModel.fromJson(e)).toList();
          _unreadCount = _notifications.where((n) => !n.isRead).length;
        } else {
          _notifications = [];
          _unreadCount = 0;
        }
      } else {
        _notifications = [];
        _unreadCount = 0;
      }
    } catch (_) {
      _notifications = [];
      _unreadCount = 0;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchUnreadCount() async {
    try {
      final response = await _api.get(ApiEndpoints.unreadNotificationCount);
      if (response.data != null && response.data['unread_count'] != null) {
        _unreadCount = int.tryParse(response.data['unread_count'].toString()) ?? 0;
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> markAsRead(int id) async {
    final idx = _notifications.indexWhere((n) => n.id == id);
    if (idx >= 0 && !_notifications[idx].isRead) {
      final old = _notifications[idx];
      _notifications[idx] = NotificationModel(
        id: old.id,
        userId: old.userId,
        category: old.category,
        title: old.title,
        body: old.body,
        data: old.data,
        imageUrl: old.imageUrl,
        actionUrl: old.actionUrl,
        isRead: true,
        readAt: DateTime.now().toIso8601String(),
        createdAt: old.createdAt,
      );
      _unreadCount = (_unreadCount - 1).clamp(0, 9999);
      notifyListeners();
    }

    try {
      await _api.patch('${ApiEndpoints.notifications}/$id/read');
    } catch (_) {}
  }

  Future<void> markAllAsRead() async {
    for (var i = 0; i < _notifications.length; i++) {
      final old = _notifications[i];
      if (!old.isRead) {
        _notifications[i] = NotificationModel(
          id: old.id,
          userId: old.userId,
          category: old.category,
          title: old.title,
          body: old.body,
          data: old.data,
          imageUrl: old.imageUrl,
          actionUrl: old.actionUrl,
          isRead: true,
          readAt: DateTime.now().toIso8601String(),
          createdAt: old.createdAt,
        );
      }
    }
    _unreadCount = 0;
    notifyListeners();

    try {
      await _api.patch(ApiEndpoints.markAllNotificationsRead);
    } catch (_) {}
  }

  void clear() {
    _notifications = [];
    _unreadCount = 0;
    notifyListeners();
  }
}
