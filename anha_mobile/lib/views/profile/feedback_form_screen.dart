import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/feedback_provider.dart';
import '../../widgets/custom_button.dart';

class FeedbackFormScreen extends StatefulWidget {
  const FeedbackFormScreen({super.key});

  @override
  State<FeedbackFormScreen> createState() => _FeedbackFormScreenState();
}

class _FeedbackFormScreenState extends State<FeedbackFormScreen> {
  String _selectedCategory = 'Pengiriman';
  final TextEditingController _msgController = TextEditingController();
  final List<String> _categories = ['Produk', 'Pengiriman', 'Pelayanan', 'Aplikasi', 'Lainnya'];
  final List<XFile> _selectedPhotos = [];
  final ImagePicker _picker = ImagePicker();
  bool _isSending = false;

  @override
  void dispose() {
    _msgController.dispose();
    super.dispose();
  }

  String _getCategoryKey(String label) {
    switch (label) {
      case 'Produk':
        return 'product';
      case 'Pengiriman':
        return 'delivery';
      case 'Pelayanan':
        return 'service';
      case 'Aplikasi':
        return 'app';
      default:
        return 'other';
    }
  }

  Future<void> _pickImage() async {
    if (_selectedPhotos.length >= 3) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Maksimal lampiran 3 foto.')),
      );
      return;
    }

    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 80,
      );
      if (image != null) {
        setState(() {
          _selectedPhotos.add(image);
        });
      }
    } catch (_) {}
  }

  void _submit() async {
    final text = _msgController.text.trim();
    if (text.length < 20) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pesan kritik & saran minimal 20 karakter.')),
      );
      return;
    }

    setState(() => _isSending = true);
    final nav = Navigator.of(context);
    final messenger = ScaffoldMessenger.of(context);

    final success = await context.read<FeedbackProvider>().sendFeedback(
      categoryKey: _getCategoryKey(_selectedCategory),
      content: text,
      photoPaths: _selectedPhotos.map((f) => f.path).toList(),
    );

    if (!mounted) return;
    setState(() => _isSending = false);

    if (success) {
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Terima kasih! Masukan Anda telah berhasil dikirim ke Admin.'),
          backgroundColor: AppColors.success,
        ),
      );
      nav.pop(true);
    } else {
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Gagal mengirim feedback. Silakan coba lagi.'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.text),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Kirim Kritik & Saran',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'KATEGORI MASUKAN',
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w800,
                color: AppColors.textMuted,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _categories.map((cat) {
                final active = _selectedCategory == cat;
                return InkWell(
                  onTap: () => setState(() => _selectedCategory = cat),
                  borderRadius: BorderRadius.circular(20),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                    decoration: BoxDecoration(
                      color: active ? AppColors.primarySoft : Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: active ? AppColors.primary : AppColors.border,
                        width: active ? 1.5 : 1,
                      ),
                    ),
                    child: Text(
                      cat,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: active ? FontWeight.w700 : FontWeight.w500,
                        color: active ? AppColors.primary : AppColors.text,
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 20),

            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'PESAN KRITIK / SARAN',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textMuted,
                    letterSpacing: 0.5,
                  ),
                ),
                Text(
                  '${_msgController.text.length} / 500 (min. 20)',
                  style: const TextStyle(fontSize: 10, color: AppColors.textLight),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Container(
              decoration: BoxDecoration(
                color: const Color(0xFFF9FAFB),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.border),
              ),
              child: TextField(
                controller: _msgController,
                maxLines: 5,
                maxLength: 500,
                onChanged: (_) => setState(() {}),
                style: const TextStyle(fontSize: 12),
                decoration: const InputDecoration(
                  hintText: 'Tuliskan masukan detail Anda untuk peningkatan kualitas layanan ANHA...',
                  hintStyle: TextStyle(fontSize: 11, color: AppColors.textLight),
                  border: InputBorder.none,
                  counterText: '',
                  contentPadding: EdgeInsets.all(12),
                ),
              ),
            ),
            const SizedBox(height: 20),

            const Text(
              'FOTO LAMPIRAN (OPSIONAL, MAKS. 3)',
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w800,
                color: AppColors.textMuted,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                ..._selectedPhotos.map((photo) {
                  return Stack(
                    children: [
                      Container(
                        width: 60,
                        height: 60,
                        margin: const EdgeInsets.only(right: 8),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.border),
                          image: DecorationImage(
                            image: FileImage(File(photo.path)),
                            fit: BoxFit.cover,
                          ),
                        ),
                      ),
                      Positioned(
                        top: -4,
                        right: 4,
                        child: InkWell(
                          onTap: () {
                            setState(() => _selectedPhotos.remove(photo));
                          },
                          child: Container(
                            padding: const EdgeInsets.all(2),
                            decoration: const BoxDecoration(
                              color: AppColors.danger,
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.close, size: 12, color: Colors.white),
                          ),
                        ),
                      ),
                    ],
                  );
                }),
                if (_selectedPhotos.length < 3)
                  InkWell(
                    onTap: _pickImage,
                    child: Container(
                      width: 60,
                      height: 60,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.add_a_photo_outlined, size: 20, color: AppColors.primary),
                            SizedBox(height: 2),
                            Text('+ Foto', style: TextStyle(fontSize: 8, color: AppColors.primary, fontWeight: FontWeight.bold)),
                          ],
                        ),
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 32),

            CustomButton(
              text: 'Kirim Feedback',
              variant: ButtonVariant.primary,
              isLoading: _isSending,
              onPressed: _submit,
            ),
          ],
        ),
      ),
    );
  }
}
