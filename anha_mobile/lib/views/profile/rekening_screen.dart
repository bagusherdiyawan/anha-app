import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../models/rekening_model.dart';

class RekeningScreen extends StatefulWidget {
  const RekeningScreen({super.key});

  @override
  State<RekeningScreen> createState() => _RekeningScreenState();
}

class _RekeningScreenState extends State<RekeningScreen> {
  final ApiClient _api = ApiClient();
  List<RekeningModel> _rekenings = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchRekening();
  }

  Future<void> _fetchRekening() async {
    setState(() => _isLoading = true);
    try {
      final response = await _api.get(ApiEndpoints.storeAccounts);
      final resData = response.data;
      if (resData != null && resData['data'] is List) {
        final list = (resData['data'] as List).map((e) => RekeningModel.fromJson(e)).toList();
        setState(() {
          _rekenings = list;
          _isLoading = false;
        });
        return;
      }
    } catch (_) {}

    setState(() {
      _rekenings = [];
      _isLoading = false;
    });
  }

  void _copyToClipboard(String text, String label) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('$label berhasil disalin ke clipboard!'),
        backgroundColor: AppColors.success,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.text),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Row(
          children: [
            Text('🏦', style: TextStyle(fontSize: 16)),
            SizedBox(width: 8),
            Text(
              'Rekening & Cara Bayar',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: AppColors.text,
              ),
            ),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Info banner
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.primarySoft,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: const Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('ℹ️', style: TextStyle(fontSize: 20)),
                        SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Informasi Pembayaran Resmi Toko',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.primary,
                                ),
                              ),
                              SizedBox(height: 4),
                              Text(
                                'ANHA Electrical melayani pembayaran tunai COD saat pesanan tiba maupun transfer antar bank resmi. Hindari pembayaran ke rekening di luar daftar ini.',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: AppColors.text,
                                  height: 1.4,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 18),

                  const Text(
                    'DAFTAR REKENING RESMI',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textMuted,
                      letterSpacing: 0.8,
                    ),
                  ),
                  if (_rekenings.isEmpty)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Column(
                        children: [
                          const Text('🏦', style: TextStyle(fontSize: 32)),
                          const SizedBox(height: 8),
                          const Text(
                            'Belum ada rekening toko terdaftar',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textMuted),
                          ),
                          const SizedBox(height: 8),
                          TextButton.icon(
                            onPressed: _fetchRekening,
                            icon: const Icon(Icons.refresh, size: 16),
                            label: const Text('Muat Ulang', style: TextStyle(fontSize: 11)),
                          ),
                        ],
                      ),
                    )
                  else
                    ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: _rekenings.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final item = _rekenings[index];
                      final isCod = item.type.toLowerCase() == 'cod';

                      return Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Container(
                                  width: 40,
                                  height: 40,
                                  decoration: BoxDecoration(
                                    color: isCod ? AppColors.yellowSoft : AppColors.primarySoft,
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Center(
                                    child: Text(
                                      isCod ? '💵' : '💳',
                                      style: const TextStyle(fontSize: 20),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        item.nama,
                                        style: const TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.w800,
                                          color: AppColors.text,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        isCod ? 'Metode Pembayaran Utama' : 'Bank Transfer',
                                        style: const TextStyle(
                                          fontSize: 10,
                                          color: AppColors.textLight,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF9FAFB),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: AppColors.border),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        isCod ? 'KODE PEMBAYARAN' : 'NOMOR REKENING',
                                        style: const TextStyle(
                                          fontSize: 9,
                                          fontWeight: FontWeight.w700,
                                          color: AppColors.textLight,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        item.nomor,
                                        style: const TextStyle(
                                          fontSize: 14,
                                          fontWeight: FontWeight.w900,
                                          color: AppColors.text,
                                          letterSpacing: 0.5,
                                        ),
                                      ),
                                    ],
                                  ),
                                  if (!isCod)
                                    IconButton(
                                      icon: const Icon(Icons.copy, size: 18, color: AppColors.primary),
                                      tooltip: 'Salin Nomor',
                                      onPressed: () => _copyToClipboard(item.nomor, item.nama),
                                    ),
                                ],
                              ),
                            ),
                            if (item.keterangan != null && item.keterangan!.isNotEmpty) ...[
                              const SizedBox(height: 8),
                              Text(
                                item.keterangan!,
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: AppColors.textMuted,
                                  height: 1.4,
                                ),
                              ),
                            ],
                          ],
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 24),

                  // CARA BAYAR GUIDE
                  const Text(
                    'PANDUAN PEMBAYARAN COD',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textMuted,
                      letterSpacing: 0.8,
                    ),
                  ),
                  const SizedBox(height: 10),

                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: const Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _InstructionStep(
                          number: '1',
                          title: 'Pilih Barang & Checkout COD',
                          description: 'Tambahkan produk listrik ke keranjang dan pilih Bayar COD saat checkout.',
                        ),
                        Divider(height: 18, color: AppColors.divider),
                        _InstructionStep(
                          number: '2',
                          title: 'Lacak Pengiriman Kurir ANHA',
                          description: 'Pantau posisi kurir toko secara real-time melalui peta interaktif OSM.',
                        ),
                        Divider(height: 18, color: AppColors.divider),
                        _InstructionStep(
                          number: '3',
                          title: 'Cek Barang & Bayar Tunai',
                          description: 'Pastikan pesanan sesuai dan serahkan uang tunai pas kepada sopir/kurir.',
                        ),
                        Divider(height: 18, color: AppColors.divider),
                        _InstructionStep(
                          number: '4',
                          title: 'Konfirmasi Terima & Dapatkan Poin',
                          description: 'Klik "Konfirmasi Terima Barang" untuk menyelesaikan pesanan dan menambah saldo poin listrik Anda.',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
    );
  }
}

class _InstructionStep extends StatelessWidget {
  final String number;
  final String title;
  final String description;

  const _InstructionStep({
    required this.number,
    required this.title,
    required this.description,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 22,
          height: 22,
          decoration: const BoxDecoration(
            color: AppColors.primary,
            shape: BoxShape.circle,
          ),
          child: Center(
            child: Text(
              number,
              style: const TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w800,
                color: Colors.white,
              ),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: AppColors.text,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                description,
                style: const TextStyle(
                  fontSize: 11,
                  color: AppColors.textMuted,
                  height: 1.3,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
