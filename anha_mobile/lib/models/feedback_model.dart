class FeedbackModel {
  final int id;
  final String category;
  final String message;
  final String status; // 'pending', 'read', 'replied'
  final String? reply;
  final String? createdAt;
  final List<String> images;

  FeedbackModel({
    required this.id,
    required this.category,
    required this.message,
    required this.status,
    this.reply,
    this.createdAt,
    this.images = const [],
  });

  bool get isReplied => reply != null && reply!.isNotEmpty;

  String get statusDisplay {
    if (isReplied) return 'Ditanggapi';
    if (status.toLowerCase() == 'read') return 'Dibaca';
    return 'Dikirim';
  }

  factory FeedbackModel.fromJson(Map<String, dynamic> json) {
    List<String> imgList = [];
    if (json['images'] is List) {
      imgList = (json['images'] as List).map((e) => e.toString()).toList();
    }

    return FeedbackModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      category: json['category'] ?? 'Lainnya',
      message: json['content'] ?? json['message'] ?? '',
      status: json['status'] ?? 'pending',
      reply: json['reply'] ?? (json['replies'] is List && (json['replies'] as List).isNotEmpty ? (json['replies'] as List).first['reply'] : null),
      createdAt: json['created_at']?.toString(),
      images: imgList,
    );
  }
}
