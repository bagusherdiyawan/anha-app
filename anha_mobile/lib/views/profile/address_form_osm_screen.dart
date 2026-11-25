import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../models/address_model.dart';
import '../../providers/address_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';

class AddressFormOsmScreen extends StatefulWidget {
  final AddressModel? initialAddress;

  const AddressFormOsmScreen({super.key, this.initialAddress});

  @override
  State<AddressFormOsmScreen> createState() => _AddressFormOsmScreenState();
}

class _AddressFormOsmScreenState extends State<AddressFormOsmScreen> {
  String _selectedLabel = 'Rumah';
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _streetController = TextEditingController();
  final TextEditingController _districtController = TextEditingController();
  final TextEditingController _cityController = TextEditingController();
  final TextEditingController _provinceController = TextEditingController();
  final TextEditingController _postalCodeController = TextEditingController();

  bool _isDefault = false;
  bool _isSaving = false;

  // Selected Pin Coordinates on OpenStreetMap
  LatLng _pinLocation = const LatLng(-6.240582, 106.629168); // Gading Serpong / Tangerang area

  @override
  void initState() {
    super.initState();
    if (widget.initialAddress != null) {
      final a = widget.initialAddress!;
      _selectedLabel = a.label;
      _nameController.text = a.recipientName;
      _phoneController.text = a.phone;
      _streetController.text = a.address;
      _districtController.text = a.district ?? '';
      _cityController.text = a.city ?? '';
      _provinceController.text = a.province ?? '';
      _postalCodeController.text = a.postalCode ?? '';
      _isDefault = a.isDefault;
      if (a.latitude != null && a.longitude != null) {
        _pinLocation = LatLng(a.latitude!, a.longitude!);
      }
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _streetController.dispose();
    _districtController.dispose();
    _cityController.dispose();
    _provinceController.dispose();
    _postalCodeController.dispose();
    super.dispose();
  }

  Future<void> _handleSave() async {
    final name = _nameController.text.trim();
    final phone = _phoneController.text.trim();
    final street = _streetController.text.trim();
    final district = _districtController.text.trim();
    final city = _cityController.text.trim();
    final province = _provinceController.text.trim();
    final postalCode = _postalCodeController.text.trim();

    if (name.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nama penerima wajib diisi!')),
      );
      return;
    }
    if (phone.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nomor handphone / WhatsApp wajib diisi!')),
      );
      return;
    }
    if (street.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Alamat lengkap jalan wajib diisi!')),
      );
      return;
    }
    if (city.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Kota / Kabupaten wajib diisi!')),
      );
      return;
    }
    if (province.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Provinsi wajib diisi!')),
      );
      return;
    }
    if (postalCode.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Kode pos wajib diisi!')),
      );
      return;
    }

    setState(() => _isSaving = true);

    final address = AddressModel(
      id: widget.initialAddress?.id ?? DateTime.now().millisecondsSinceEpoch % 10000,
      label: _selectedLabel,
      recipientName: name,
      phone: phone,
      address: street,
      district: district.isNotEmpty ? district : null,
      city: city,
      province: province,
      postalCode: postalCode,
      isDefault: _isDefault,
      latitude: _pinLocation.latitude,
      longitude: _pinLocation.longitude,
    );

    final addressProvider = context.read<AddressProvider>();
    final bool success;
    if (widget.initialAddress != null) {
      success = await addressProvider.updateAddress(widget.initialAddress!.id, address);
    } else {
      success = await addressProvider.addAddress(address);
    }

    if (!mounted) return;
    setState(() => _isSaving = false);

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Alamat pengiriman berhasil disimpan!'),
          backgroundColor: AppColors.success,
        ),
      );
      Navigator.pop(context);
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
        title: Text(
          widget.initialAddress != null ? 'Edit Alamat' : 'Tambah Alamat Pengiriman',
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Label Selector
            const Text(
              'LABEL ALAMAT',
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
                _buildLabelChoice('Rumah', '🏠'),
                const SizedBox(width: 8),
                _buildLabelChoice('Kantor', '🏢'),
                const SizedBox(width: 8),
                _buildLabelChoice('Proyek', '🏗️'),
              ],
            ),
            const SizedBox(height: 16),

            // Form inputs
            CustomTextField(
              label: 'Nama Penerima',
              hint: 'Nama lengkap penerima',
              controller: _nameController,
              prefixIcon: const Icon(Icons.person_outline, size: 20, color: AppColors.textLight),
            ),
            const SizedBox(height: 12),

            CustomTextField(
              label: 'No. Handphone / WhatsApp',
              hint: 'Contoh: 0812-xxxx-xxxx',
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              prefixIcon: const Icon(Icons.phone_outlined, size: 20, color: AppColors.textLight),
            ),
            const SizedBox(height: 12),

            CustomTextField(
              label: 'Alamat Lengkap (Jalan, No, RT/RW)',
              hint: 'Jl. Nama jalan, Nomor, Patokan...',
              controller: _streetController,
              maxLines: 2,
              prefixIcon: const Icon(Icons.location_on_outlined, size: 20, color: AppColors.textLight),
            ),
            const SizedBox(height: 12),

            Row(
              children: [
                Expanded(
                  child: CustomTextField(
                    label: 'Kota / Kab',
                    hint: 'Kota',
                    controller: _cityController,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: CustomTextField(
                    label: 'Provinsi',
                    hint: 'Provinsi',
                    controller: _provinceController,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            CustomTextField(
              label: 'Kode Pos',
              hint: '5 digit kode pos',
              controller: _postalCodeController,
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 20),

            // OPENSTREETMAP (OSM) INTERACTIVE PICKER SECTION
            const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'PIN TITIK LOKASI (OPENSTREETMAP)',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                    color: AppColors.primary,
                    letterSpacing: 0.5,
                  ),
                ),
                Text(
                  'Ketuk peta untuk geser pin 📍',
                  style: TextStyle(fontSize: 10, color: AppColors.textLight),
                ),
              ],
            ),
            const SizedBox(height: 8),

            Container(
              height: 200,
              width: double.infinity,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.primary, width: 1.5),
              ),
              clipBehavior: Clip.antiAlias,
              child: Stack(
                children: [
                  FlutterMap(
                    options: MapOptions(
                      initialCenter: _pinLocation,
                      initialZoom: 14.0,
                      onTap: (tapPosition, point) {
                        setState(() {
                          _pinLocation = point;
                        });
                      },
                    ),
                    children: [
                      TileLayer(
                        urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                        userAgentPackageName: 'com.electrical.anha_mobile',
                      ),
                      MarkerLayer(
                        markers: [
                          Marker(
                            point: _pinLocation,
                            width: 50,
                            height: 50,
                            child: const Center(
                              child: Text('📍', style: TextStyle(fontSize: 32)),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Positioned(
                    bottom: 8,
                    left: 8,
                    right: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.92),
                        borderRadius: BorderRadius.circular(8),
                        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4)],
                      ),
                      child: Text(
                        'Koordinat: ${_pinLocation.latitude.toStringAsFixed(6)}, ${_pinLocation.longitude.toStringAsFixed(6)}',
                        style: const TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.w700,
                          color: AppColors.primary,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Toggle Set Default Switch
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF9FAFB),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Jadikan Alamat Utama',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: AppColors.text,
                        ),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Digunakan otomatis saat checkout pesanan',
                        style: TextStyle(fontSize: 10, color: AppColors.textMuted),
                      ),
                    ],
                  ),
                  Switch(
                    value: _isDefault,
                    activeColor: AppColors.primary,
                    onChanged: (val) => setState(() => _isDefault = val),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Save Button
            CustomButton(
              text: 'Simpan Alamat',
              variant: ButtonVariant.primary,
              isLoading: _isSaving,
              onPressed: _handleSave,
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildLabelChoice(String label, String icon) {
    final active = _selectedLabel == label;
    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _selectedLabel = label),
        borderRadius: BorderRadius.circular(8),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          decoration: BoxDecoration(
            color: active ? AppColors.primarySoft : Colors.white,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(
              color: active ? AppColors.primary : AppColors.border,
              width: active ? 1.5 : 1,
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(icon, style: const TextStyle(fontSize: 14)),
              const SizedBox(width: 4),
              Text(
                label,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: active ? AppColors.primary : AppColors.text,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
