import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/address_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/status_badge.dart';
import 'address_form_osm_screen.dart';

class AddressListScreen extends StatefulWidget {
  const AddressListScreen({super.key});

  @override
  State<AddressListScreen> createState() => _AddressListScreenState();
}

class _AddressListScreenState extends State<AddressListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AddressProvider>().fetchAddresses();
    });
  }

  @override
  Widget build(BuildContext context) {
    final addressProvider = context.watch<AddressProvider>();
    final addresses = addressProvider.addresses;

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
          'Alamat Pengiriman',
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
            // Tambah Alamat Baru Button
            CustomButton(
              text: 'Tambah Alamat Baru (Peta OSM)',
              variant: ButtonVariant.outline,
              icon: const Icon(Icons.add_location_alt_outlined, size: 18),
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const AddressFormOsmScreen()),
                );
              },
            ),
            const SizedBox(height: 16),

            // Addresses List
            if (addressProvider.isLoading)
              const Center(child: Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator()))
            else if (addresses.isEmpty)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: Text('Belum ada alamat tersimpan'),
                ),
              )
            else
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: addresses.length,
                separatorBuilder: (_, __) => const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  final item = addresses[index];
                  final isDef = item.isDefault;

                  return Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isDef ? AppColors.primary : AppColors.border,
                        width: isDef ? 1.5 : 1,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.02),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                StatusBadge(
                                  text: item.label.toUpperCase(),
                                  type: isDef ? BadgeType.dark : BadgeType.gray,
                                ),
                                if (isDef) ...[
                                  const SizedBox(width: 6),
                                  const StatusBadge(text: 'Default', type: BadgeType.green),
                                ],
                              ],
                            ),
                            // Quick Select for checkout
                            InkWell(
                              onTap: () {
                                addressProvider.selectAddress(item);
                                Navigator.pop(context);
                              },
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: AppColors.primarySoft,
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: const Text(
                                  'Pilih',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: AppColors.primary,
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          '${item.recipientName} · ${item.phone}',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                            color: AppColors.text,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          item.fullAddress,
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppColors.textMuted,
                            height: 1.4,
                          ),
                        ),
                        if (item.latitude != null) ...[
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              const Text('📍 ', style: TextStyle(fontSize: 10)),
                              Text(
                                'Pin OSM: ${item.latitude?.toStringAsFixed(4)}, ${item.longitude?.toStringAsFixed(4)}',
                                style: const TextStyle(fontSize: 9, color: AppColors.textLight),
                              ),
                            ],
                          ),
                        ],
                        const Divider(height: 18, color: AppColors.divider),
                        Row(
                          children: [
                            InkWell(
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => AddressFormOsmScreen(initialAddress: item),
                                  ),
                                );
                              },
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  border: Border.all(color: AppColors.primary),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  'Edit',
                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.primary),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            InkWell(
                              onTap: () => addressProvider.deleteAddress(item.id),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  border: Border.all(color: AppColors.border),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  'Hapus',
                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textLight),
                                ),
                              ),
                            ),
                            if (!isDef) ...[
                              const Spacer(),
                              InkWell(
                                onTap: () => addressProvider.setDefaultAddress(item.id),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                  decoration: BoxDecoration(
                                    border: Border.all(color: AppColors.border),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Text(
                                    'Set Default',
                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.text),
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
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
