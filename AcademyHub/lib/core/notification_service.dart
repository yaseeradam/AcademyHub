import 'package:flutter/services.dart';

class NotificationService {
  static const MethodChannel _channel = MethodChannel('com.academyhub/notifications');

  static Future<void> init() async {
    try {
      await requestPermission();
    } catch (_) {}
  }

  static Future<bool> requestPermission() async {
    try {
      final bool? granted = await _channel.invokeMethod<bool>('requestPermission');
      return granted ?? false;
    } on PlatformException catch (_) {
      return false;
    }
  }

  static Future<void> showNotification({
    required int id,
    required String title,
    required String body,
  }) async {
    try {
      await _channel.invokeMethod('showNotification', {
        'id': id,
        'title': title,
        'body': body,
      });
    } on PlatformException catch (_) {}
  }
}
