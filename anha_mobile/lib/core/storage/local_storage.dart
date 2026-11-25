import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class LocalStorage {
  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'auth_user';
  static const String _keyOnboarding = 'onboarding_seen';

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyToken, token);
  }

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_keyToken);
  }

  static Future<void> saveUser(Map<String, dynamic> userMap) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyUser, jsonEncode(userMap));
  }

  static Future<Map<String, dynamic>?> getUser() async {
    final prefs = await SharedPreferences.getInstance();
    final str = prefs.getString(_keyUser);
    if (str != null) {
      try {
        return jsonDecode(str) as Map<String, dynamic>;
      } catch (_) {
        return null;
      }
    }
    return null;
  }

  static Future<void> setOnboardingSeen(bool seen) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_keyOnboarding, seen);
  }

  static Future<bool> isOnboardingSeen() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_keyOnboarding) ?? false;
  }

  static Future<void> clearAuth() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyToken);
    await prefs.remove(_keyUser);
  }
}
