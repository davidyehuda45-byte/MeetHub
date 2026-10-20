import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/constants.dart';
import '../models/meeting_model.dart';
import '../models/user_model.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  String _baseUrl = AppConstants.defaultBaseUrl;
  String? _token;
  UserModel? _currentUser;

  UserModel? get currentUser => _currentUser;
  bool get isAuthenticated => _token != null;

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(AppConstants.tokenKey);
    final userJson = prefs.getString(AppConstants.userKey);
    if (userJson != null) {
      try {
        _currentUser = UserModel.fromJson(jsonDecode(userJson));
      } catch (_) {}
    }
    final customUrl = prefs.getString(AppConstants.customServerUrlKey);
    if (customUrl != null && customUrl.isNotEmpty) {
      _baseUrl = customUrl;
    }
  }

  void setBaseUrl(String url) async {
    _baseUrl = url.endsWith('/api/v1') ? url : '$url/api/v1';
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(AppConstants.customServerUrlKey, _baseUrl);
  }

  Map<String, String> _headers({bool needsAuth = true}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (needsAuth && _token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }
    return headers;
  }

  // --- Auth ---

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/auth/login'),
      headers: _headers(needsAuth: false),
      body: jsonEncode({'email': email, 'password': password, 'device_name': 'flutter_mobile'}),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200 && data['status'] == 'success') {
      _token = data['token'];
      _currentUser = UserModel.fromJson(data['user']);

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(AppConstants.tokenKey, _token!);
      await prefs.setString(AppConstants.userKey, jsonEncode(_currentUser!.toJson()));
      return {'success': true};
    }

    return {'success': false, 'message': data['message'] ?? 'Login gagal'};
  }

  Future<Map<String, dynamic>> register(String name, String email, String password) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/auth/register'),
      headers: _headers(needsAuth: false),
      body: jsonEncode({'name': name, 'email': email, 'password': password}),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 201 && data['status'] == 'success') {
      _token = data['token'];
      _currentUser = UserModel.fromJson(data['user']);

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(AppConstants.tokenKey, _token!);
      await prefs.setString(AppConstants.userKey, jsonEncode(_currentUser!.toJson()));
      return {'success': true};
    }

    return {'success': false, 'message': data['message'] ?? 'Registrasi gagal'};
  }

  Future<void> logout() async {
    try {
      await http.post(Uri.parse('$_baseUrl/auth/logout'), headers: _headers());
    } catch (_) {}

    _token = null;
    _currentUser = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(AppConstants.tokenKey);
    await prefs.remove(AppConstants.userKey);
  }

  // --- Meetings ---

  Future<List<MeetingModel>> getMeetings() async {
    final response = await http.get(Uri.parse('$_baseUrl/meetings'), headers: _headers());
    if (response.statusCode == 200) {
      final body = jsonDecode(response.body);
      final list = body['data']['data'] as List? ?? [];
      return list.map((item) => MeetingModel.fromJson(item)).toList();
    }
    return [];
  }

  Future<MeetingModel?> createInstantMeeting(String? title) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/meetings/instant'),
      headers: _headers(),
      body: jsonEncode({'title': title}),
    );

    if (response.statusCode == 201) {
      final body = jsonDecode(response.body);
      return MeetingModel.fromJson(body['meeting']);
    }
    return null;
  }

  Future<MeetingModel?> createScheduledMeeting(Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/meetings'),
      headers: _headers(),
      body: jsonEncode(data),
    );

    if (response.statusCode == 201) {
      final body = jsonDecode(response.body);
      return MeetingModel.fromJson(body['meeting']);
    }
    return null;
  }

  Future<Map<String, dynamic>> joinMeeting(String code, {String? guestName, String? password}) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/meetings/$code/join'),
      headers: _headers(needsAuth: _token != null),
      body: jsonEncode({
        'guest_name': guestName,
        'password': password,
        'platform': 'android',
      }),
    );

    return jsonDecode(response.body);
  }

  Future<void> sendHeartbeat(String sessionToken) async {
    try {
      await http.post(
        Uri.parse('$_baseUrl/meetings/attendance/heartbeat'),
        headers: _headers(needsAuth: false),
        body: jsonEncode({'session_token': sessionToken}),
      );
    } catch (_) {}
  }

  Future<void> leaveMeeting(String sessionToken) async {
    try {
      await http.post(
        Uri.parse('$_baseUrl/meetings/attendance/leave'),
        headers: _headers(needsAuth: false),
        body: jsonEncode({'session_token': sessionToken}),
      );
    } catch (_) {}
  }
}

