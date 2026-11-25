import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final ImagePicker _picker = ImagePicker();

  File? _localImageFile;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().currentUser;
    if (user != null) {
      _nameController.text = user.name;
      _phoneController.text = user.phone ?? '';
      _emailController.text = user.email ?? user.subscriptionNumber ?? '';
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _pickPhoto() async {
    try {
      final XFile? picked = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 800,
        maxHeight: 800,
        imageQuality: 85,
      );
      if (picked != null) {
        setState(() {
          _localImageFile = File(picked.path);
        });
        if (mounted) {
          final auth = context.read<AuthProvider>();
          final ok = await auth.updatePhoto(picked.path);
          if (ok && mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Foto profil berhasil diunggah ke server!'),
                backgroundColor: AppColors.success,
              ),
            );
          } else if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(auth.errorMessage ?? 'Gagal mengunggah foto profil ke server.'),
                backgroundColor: AppColors.danger,
              ),
            );
          }
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal memilih foto: $e'),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    }
  }

  Future<void> _handleSave() async {
    final name = _nameController.text.trim();
    final phone = _phoneController.text.trim();

    if (name.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nama lengkap wajib diisi!')),
      );
      return;
    }

    if (phone.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nomor handphone wajib diisi!')),
      );
      return;
    }

    setState(() => _isSaving = true);
    final authProvider = context.read<AuthProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final nav = Navigator.of(context);

    final success = await authProvider.updateProfile(
      name: name,
      phone: phone,
    );

    if (!mounted) return;
    setState(() => _isSaving = false);

    if (success) {
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Profil berhasil diperbarui di server ANHA!'),
          backgroundColor: AppColors.success,
        ),
      );
      nav.pop();
    } else {
      messenger.showSnackBar(
        SnackBar(
          content: Text(authProvider.errorMessage ?? 'Gagal memperbarui profil.'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().currentUser;

    ImageProvider? avatarProvider;
    if (_localImageFile != null) {
      avatarProvider = FileImage(_localImageFile!);
    } else if (user?.photoUrl != null && user!.photoUrl!.isNotEmpty) {
      final pUrl = user.photoUrl!.startsWith('http')
          ? user.photoUrl!
          : '${ApiEndpoints.storageUrl}${user.photoUrl}';
      avatarProvider = NetworkImage(pUrl);
    }

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
          'Edit Profil',
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
          children: [
            // Avatar with edit badge
            Center(
              child: InkWell(
                onTap: _pickPhoto,
                borderRadius: BorderRadius.circular(40),
                child: Stack(
                  children: [
                    Container(
                      width: 76,
                      height: 76,
                      decoration: BoxDecoration(
                        color: AppColors.yellow,
                        shape: BoxShape.circle,
                        image: avatarProvider != null
                            ? DecorationImage(image: avatarProvider, fit: BoxFit.cover)
                            : null,
                      ),
                      child: avatarProvider == null
                          ? Center(
                              child: Text(
                                user != null && user.name.isNotEmpty
                                    ? user.name.split(' ').map((e) => e.isNotEmpty ? e[0] : '').take(2).join()
                                    : 'BD',
                                style: const TextStyle(
                                  fontSize: 26,
                                  fontWeight: FontWeight.w900,
                                  color: AppColors.text,
                                ),
                              ),
                            )
                          : null,
                    ),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: Container(
                        width: 26,
                        height: 26,
                        decoration: BoxDecoration(
                          color: AppColors.primary,
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 2),
                        ),
                        child: const Center(
                          child: Icon(Icons.edit, size: 13, color: Colors.white),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 8),
            InkWell(
              onTap: _pickPhoto,
              child: const Text(
                'Ubah Foto Profil',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: AppColors.primary,
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Form Inputs
            CustomTextField(
              label: 'Nama Lengkap',
              hint: 'Nama Anda',
              controller: _nameController,
              prefixIcon: const Icon(Icons.person_outline, size: 20, color: AppColors.textLight),
            ),
            const SizedBox(height: 14),

            CustomTextField(
              label: 'Kode Akun / Nomor Langganan',
              hint: 'Kode Akses',
              controller: _emailController,
              readOnly: true,
              prefixIcon: const Icon(Icons.vpn_key_outlined, size: 20, color: AppColors.textLight),
            ),
            const Padding(
              padding: EdgeInsets.only(top: 4, left: 4),
              child: Text(
                '*Kode akun dikelola langsung oleh Admin Website.',
                style: TextStyle(fontSize: 10, color: AppColors.textLight),
              ),
            ),
            const SizedBox(height: 14),

            CustomTextField(
              label: 'No. Handphone / WhatsApp',
              hint: '08xx-xxxx-xxxx',
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              prefixIcon: const Icon(Icons.phone_outlined, size: 20, color: AppColors.textLight),
            ),
            const SizedBox(height: 28),

            CustomButton(
              text: 'Simpan Perubahan',
              variant: ButtonVariant.primary,
              isLoading: _isSaving,
              onPressed: _handleSave,
            ),
          ],
        ),
      ),
    );
  }
}
