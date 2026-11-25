class NotificationModel {
  final int id;
  final int userId;
  final String? category;
  final String title;
  final String body;
  final Map<String, dynamic>? data;
  final String? imageUrl;
  final String? actionUrl;
  final bool isRead;
  final String? readAt;
  final String? createdAt;

  NotificationModel({
    required this.id,
    required this.userId,
    this.category,
    required this.title,
    required this.body,
    this.data,
    this.imageUrl,
    this.actionUrl,
    this.isRead = false,
    this.readAt,
    this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      userId: json['user_id'] is int ? json['user_id'] : int.tryParse(json['user_id']?.toString() ?? '0') ?? 0,
      category: json['category']?.toString(),
      title: json['title']?.toString() ?? '',
      body: json['body']?.toString() ?? '',
      data: json['data'] is Map<String, dynamic> ? json['data'] : null,
      imageUrl: json['image_url']?.toString(),
      actionUrl: json['action_url']?.toString(),
      isRead: json['is_read'] == true || json['is_read'] == 1,
      readAt: json['read_at']?.toString(),
      createdAt: json['created_at']?.toString(),
    );
  }

  String get timeFormatted {
    if (createdAt == null || createdAt!.isEmpty) return '';
    try {
      final dt = DateTime.parse(createdAt!);
      final now = DateTime.now();
      final diff = now.difference(dt);
      if (diff.inMinutes < 60) {
        return '${diff.inMinutes} mnt yang lalu';
      } else if (diff.inHours < 24) {
        return '${diff.inHours} jam yang lalu';
      } else {
        return '${dt.day}/${dt.month}/${dt.year}';
      }
    } catch (_) {
      return createdAt!;
    }
  }

  String get iconEmoji {
    final cat = category?.toLowerCase() ?? '';
    if (cat.contains('order') || cat.contains('pesanan') || cat.contains('delivery')) {
      return '🚚';
    } else if (cat.contains('point') || cat.contains('loyalty') || cat.contains('poin')) {
      return '⚡';
    } else if (cat.contains('reward') || cat.contains('promo')) {
      return '🎁';
    } else {
      return '📢';
    }
  }
}
