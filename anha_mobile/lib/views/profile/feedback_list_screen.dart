import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/feedback_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/status_badge.dart';
import 'feedback_form_screen.dart';

class FeedbackListScreen extends StatefulWidget {
  const FeedbackListScreen({super.key});

  @override
  State<FeedbackListScreen> createState() => _FeedbackListScreenState();
}

class _FeedbackListScreenState extends State<FeedbackListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FeedbackProvider>().fetchFeedbacks();
    });
  }

  @override
  Widget build(BuildContext context) {
    final feedbackProvider = context.watch<FeedbackProvider>();
    final feedbacks = feedbackProvider.feedbacks;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.text),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Kritik & Saran',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            CustomButton(
              text: 'Kirim Kritik atau Saran',
              variant: ButtonVariant.yellow,
              icon: const Text('✍️', style: TextStyle(fontSize: 16)),
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const FeedbackFormScreen()),
                );
              },
            ),
            const SizedBox(height: 16),

            if (feedbackProvider.isLoading && feedbacks.isEmpty)
              const Center(child: Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator()))
            else if (feedbacks.isEmpty)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: Text(
                    'Belum ada kritik & saran yang dikirim',
                    style: TextStyle(fontSize: 13, color: AppColors.textMuted),
                  ),
                ),
              )
            else
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: feedbacks.length,
                separatorBuilder: (_, __) => const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  final fb = feedbacks[index];
                  return Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            StatusBadge(text: fb.category, type: BadgeType.blue),
                            StatusBadge(
                              text: fb.statusDisplay,
                              type: fb.isReplied ? BadgeType.green : BadgeType.gray,
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          fb.message,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppColors.text,
                            height: 1.4,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          fb.createdAt ?? '',
                          style: const TextStyle(fontSize: 9, color: AppColors.textLight),
                        ),
                        if (fb.reply != null) ...[
                          const SizedBox(height: 10),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: AppColors.primarySoft,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Balasan Admin ANHA:',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: AppColors.primary,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  fb.reply!,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    color: AppColors.text,
                                    height: 1.4,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  );
                },
              ),
          ],
        ),
      ),
    );
  }
}
