import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../providers/auth_provider.dart';
import '../../providers/loyalty_provider.dart';
import '../../providers/order_provider.dart';
import '../../widgets/custom_button.dart';

class OrderTrackingScreen extends StatefulWidget {
  final int orderId;
  final String invoiceNumber;

  const OrderTrackingScreen({
    super.key,
    required this.orderId,
    required this.invoiceNumber,
  });

  @override
  State<OrderTrackingScreen> createState() => _OrderTrackingScreenState();
}

class _OrderTrackingScreenState extends State<OrderTrackingScreen> {
  // OSM Map Coordinates
  final LatLng _shopLocation = const LatLng(-6.229728, 106.689431); // ANHA Electrical Store
  final LatLng _defaultCustomerLocation = const LatLng(-6.240582, 106.629168); // Customer Address
  final LatLng _courierLocation = const LatLng(-6.234500, 106.655000); // Courier Live Location

  bool _isConfirming = false;
  bool _isReceived = false;
  bool _isLoadingDetail = false;

  @override
  void initState() {
    super.initState();
    _loadOrderDetail();
  }

  void _loadOrderDetail() async {
    setState(() => _isLoadingDetail = true);
    final loaded = await context.read<OrderProvider>().fetchOrderDetail(widget.orderId);
    if (mounted && loaded != null && loaded.status.toLowerCase() == 'completed') {
      context.read<LoyaltyProvider>().fetchAllLoyaltyData(silent: true);
    }
    if (mounted) {
      setState(() => _isLoadingDetail = false);
    }
  }

  void _chatDriver(String? phone, String? name) async {
    final rawPhone = (phone != null && phone.trim().isNotEmpty)
        ? phone.trim()
        : context.read<AuthProvider>().whatsappNumber;
    if (rawPhone.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nomor WhatsApp kurir/admin belum tersedia.')),
      );
      return;
    }
    final cleanPhone = rawPhone.startsWith('0') ? '62${rawPhone.substring(1)}' : rawPhone;
    final driverName = name ?? 'Kurir ANHA';
    final msg = Uri.encodeComponent('Halo $driverName, saya penerima pesanan ${widget.invoiceNumber}.');
    final url = Uri.parse('https://wa.me/$cleanPhone?text=$msg');
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final order = context.watch<OrderProvider>().orders.where((o) => o.id == widget.orderId).firstOrNull ?? context.watch<OrderProvider>().currentOrder;
    final int currentStep = _isReceived ? 5 : (order?.progressStep ?? 1);
    final String status = order?.status.toLowerCase() ?? 'pending';
    final customerLatLng = (order?.alamatPengiriman?.latitude != null && order?.alamatPengiriman?.longitude != null)
        ? LatLng(order!.alamatPengiriman!.latitude!, order.alamatPengiriman!.longitude!)
        : _defaultCustomerLocation;

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
          'Status Pesanan ${widget.invoiceNumber}',
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Blue Banner from wireframe
            Container(
              padding: const EdgeInsets.all(16),
              color: AppColors.primary,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'STATUS PESANAN',
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.w700,
                          color: Colors.white70,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        order?.statusLabel ?? (_isReceived ? 'Selesai' : 'Diproses'),
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w900,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Container(
                            width: 6,
                            height: 6,
                            decoration: const BoxDecoration(
                              color: AppColors.yellow,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            _isReceived
                                ? 'Barang telah diterima'
                                : currentStep >= 3
                                    ? 'Paket dalam pengiriman oleh kurir'
                                    : 'Pesanan sedang dipersiapkan oleh toko',
                            style: const TextStyle(
                              fontSize: 11,
                              color: Colors.white,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const Text('🚚', style: TextStyle(fontSize: 34)),
                ],
              ),
            ),

            // LIVE OPENSTREETMAP (OSM) SECTION
            Container(
              height: 200,
              width: double.infinity,
              decoration: const BoxDecoration(
                border: Border(bottom: BorderSide(color: AppColors.border)),
              ),
              child: Stack(
                children: [
                  FlutterMap(
                    options: MapOptions(
                      initialCenter: _courierLocation,
                      initialZoom: 13.0,
                    ),
                    children: [
                      TileLayer(
                        urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                        userAgentPackageName: 'com.electrical.anha_mobile',
                      ),
                      PolylineLayer(
                        polylines: [
                          Polyline(
                            points: [_shopLocation, _courierLocation, customerLatLng],
                            strokeWidth: 4.0,
                            color: AppColors.accent,
                          ),
                        ],
                      ),
                      MarkerLayer(
                        markers: [
                          // Toko Marker
                          Marker(
                            point: _shopLocation,
                            width: 48,
                            height: 54,
                            child: const Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text('🏪', style: TextStyle(fontSize: 20)),
                                Text('Toko', style: TextStyle(fontSize: 8, fontWeight: FontWeight.bold, backgroundColor: Colors.white)),
                              ],
                            ),
                          ),
                          // Kurir Live Marker
                          Marker(
                            point: _courierLocation,
                            width: 44,
                            height: 44,
                            child: Container(
                              decoration: BoxDecoration(
                                color: AppColors.yellow,
                                shape: BoxShape.circle,
                                border: Border.all(color: AppColors.primary, width: 2),
                              ),
                              child: const Center(
                                child: Text('🚚', style: TextStyle(fontSize: 20)),
                              ),
                            ),
                          ),
                          // Customer Marker
                          Marker(
                            point: customerLatLng,
                            width: 48,
                            height: 54,
                            child: const Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text('📍', style: TextStyle(fontSize: 20)),
                                Text('Tujuan', style: TextStyle(fontSize: 8, fontWeight: FontWeight.bold, backgroundColor: Colors.white)),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Positioned(
                    top: 10,
                    right: 10,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.9),
                        borderRadius: BorderRadius.circular(6),
                        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4)],
                      ),
                      child: const Row(
                        children: [
                          Text('🗺️ ', style: TextStyle(fontSize: 10)),
                          Text(
                            'OpenStreetMap (OSM)',
                            style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: AppColors.primary),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // Driver Card
            Container(
              padding: const EdgeInsets.all(14),
              color: Colors.white,
              child: Row(
                children: [
                  Container(
                    width: 42,
                    height: 42,
                    decoration: const BoxDecoration(
                      color: AppColors.primary,
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        order?.driverName != null && order!.driverName!.isNotEmpty
                            ? order.driverName!.split(' ').map((e) => e.isNotEmpty ? e[0] : '').take(2).join()
                            : 'AN',
                        style: const TextStyle(
                          fontSize: 14,
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
                          order?.driverName ?? 'Kurir Toko ANHA Electrical',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w800,
                            color: AppColors.text,
                          ),
                        ),
                        const SizedBox(height: 2),
                        const Text(
                          'Mitra Pengiriman Toko ANHA Electrical',
                          style: TextStyle(fontSize: 10, color: AppColors.textMuted),
                        ),
                      ],
                    ),
                  ),
                  // Chat button
                  IconButton(
                    icon: const Text('💬', style: TextStyle(fontSize: 18)),
                    onPressed: () => _chatDriver(order?.driverPhone, order?.driverName),
                  ),
                ],
              ),
            ),
            Container(height: 8, color: AppColors.divider),

            // 5-Stage Step Tracker (matching wireframe)
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'PROGRESS PENGIRIMAN',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textLight,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 14),
                  _buildTrackerStep(
                    icon: '✓',
                    title: 'Confirmed (Terkonfirmasi)',
                    desc: 'Pesanan telah diterima toko',
                    isDone: currentStep >= 1,
                    isActive: currentStep == 1,
                  ),
                  _buildTrackerLine(isDone: currentStep >= 2),
                  _buildTrackerStep(
                    icon: currentStep >= 2 ? '✓' : '📦',
                    title: 'Processing (Diproses)',
                    desc: 'Barang dikemas rapi bubble wrap',
                    isDone: currentStep >= 2,
                    isActive: currentStep == 2,
                  ),
                  _buildTrackerLine(isDone: currentStep >= 3),
                  _buildTrackerStep(
                    icon: '🚚',
                    title: 'In Delivery (Dalam Pengiriman)',
                    desc: 'Kurir menuju lokasi Anda',
                    isDone: currentStep >= 3,
                    isActive: currentStep == 3,
                  ),
                  _buildTrackerLine(isDone: currentStep >= 4),
                  _buildTrackerStep(
                    icon: '💳',
                    title: 'COD Payment (Bayar Tunai)',
                    desc: 'Siapkan uang pas ${CurrencyFormatter.formatRupiah(order?.total ?? 0)} saat kurir tiba',
                    isDone: currentStep >= 4,
                    isActive: currentStep == 4,
                  ),
                  _buildTrackerLine(isDone: currentStep >= 5),
                  _buildTrackerStep(
                    icon: '✓',
                    title: 'Done (Pesanan Selesai)',
                    desc: 'Barang diterima dan pembayaran tuntas',
                    isDone: currentStep >= 5,
                    isActive: currentStep >= 5,
                  ),
                ],
              ),
            ),
            Container(height: 8, color: AppColors.divider),

            // Order Detail Items
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'RINCIAN ITEM (${order?.items.length ?? 0} ITEMS)',
                    style: const TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textLight,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 10),
                  if (_isLoadingDetail) ...[
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 24),
                      child: Center(child: CircularProgressIndicator()),
                    ),
                  ] else if (order != null && order.items.isNotEmpty) ...[
                    ...order.items.map((item) => Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: _buildItemRow(
                            '💡',
                            item.barang?.namaBarang ?? 'Produk Listrik ANHA',
                            '${item.qty}× ${item.barang?.satuan ?? 'pcs'}',
                            CurrencyFormatter.formatRupiah(item.harga * item.qty),
                          ),
                        )),
                    const Divider(height: 20, color: AppColors.border),
                    if (order.shippingCost > 0) ...[
                      _buildPriceRow('Ongkos Kirim', CurrencyFormatter.formatRupiah(order.shippingCost)),
                      const SizedBox(height: 6),
                    ],
                    _buildPriceRow('Total Belanja', CurrencyFormatter.formatRupiah(order.total), isBold: true),
                  ] else ...[
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      child: Text(
                        'Rincian barang untuk pesanan ${widget.invoiceNumber} telah tercatat.',
                        style: const TextStyle(fontSize: 12, color: AppColors.textLight),
                      ),
                    ),
                    if (order != null && order.total > 0) ...[
                      const Divider(height: 20, color: AppColors.border),
                      if (order.shippingCost > 0) ...[
                        _buildPriceRow('Ongkos Kirim', CurrencyFormatter.formatRupiah(order.shippingCost)),
                        const SizedBox(height: 6),
                      ],
                      _buildPriceRow('Total Belanja', CurrencyFormatter.formatRupiah(order.total), isBold: true),
                    ],
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.border)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (status == 'delivering')
              CustomButton(
                text: _isReceived ? 'Barang Sudah Diterima ✅' : 'Konfirmasi Terima Barang',
                variant: ButtonVariant.yellow,
                isLoading: _isConfirming,
                onPressed: _isReceived
                    ? null
                    : () async {
                        final messenger = ScaffoldMessenger.of(context);
                        final orderProvider = context.read<OrderProvider>();
                        final loyaltyProvider = context.read<LoyaltyProvider>();
                        final authProvider = context.read<AuthProvider>();
                        setState(() => _isConfirming = true);
                        final ok = await orderProvider.confirmReceived(widget.orderId);
                        if (ok) {
                          await Future.wait([
                            loyaltyProvider.fetchAllLoyaltyData(silent: true),
                            orderProvider.fetchOrders(),
                            authProvider.fetchProfile(),
                          ]);
                        }
                        if (!mounted) return;
                        setState(() {
                          _isConfirming = false;
                          if (ok) {
                            _isReceived = true;
                          }
                        });
                        messenger.showSnackBar(
                          SnackBar(
                            content: Text(ok ? 'Pesanan selesai dan poin berhasil dikreditkan ke akun Anda!' : 'Konfirmasi diterima tersimpan.'),
                            backgroundColor: AppColors.success,
                          ),
                        );
                      },
              )
            else if (status == 'pending' || status == 'paid')
              CustomButton(
                text: 'Batalkan Pesanan',
                variant: ButtonVariant.danger,
                isLoading: _isConfirming,
                onPressed: () async {
                  final messenger = ScaffoldMessenger.of(context);
                  final orderProvider = context.read<OrderProvider>();
                  final confirm = await showDialog<bool>(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      title: const Text('Batalkan Pesanan?'),
                      content: const Text('Apakah Anda yakin ingin membatalkan pesanan ini? Stok akan dikembalikan ke toko.'),
                      actions: [
                        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Tidak')),
                        TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Ya, Batalkan', style: TextStyle(color: AppColors.danger))),
                      ],
                    ),
                  );
                  if (confirm == true && mounted) {
                    setState(() => _isConfirming = true);
                    final ok = await orderProvider.cancelOrder(widget.orderId);
                    if (!mounted) return;
                    setState(() => _isConfirming = false);
                    messenger.showSnackBar(
                      SnackBar(
                        content: Text(ok ? 'Pesanan berhasil dibatalkan.' : (orderProvider.errorMessage ?? 'Gagal membatalkan pesanan.')),
                        backgroundColor: ok ? AppColors.warning : AppColors.danger,
                      ),
                    );
                  }
                },
              )
            else if (status == 'completed' || status == 'selesai')
              const Center(
                child: Padding(
                  padding: EdgeInsets.symmetric(vertical: 8),
                  child: Text(
                    'Pesanan Telah Selesai ✅',
                    style: TextStyle(fontWeight: FontWeight.w800, color: AppColors.success, fontSize: 13),
                  ),
                ),
              )
            else if (status == 'cancelled' || status == 'dibatalkan')
              const Center(
                child: Padding(
                  padding: EdgeInsets.symmetric(vertical: 8),
                  child: Text(
                    'Pesanan Dibatalkan ❌',
                    style: TextStyle(fontWeight: FontWeight.w800, color: AppColors.danger, fontSize: 13),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildTrackerStep({
    required String icon,
    required String title,
    required String desc,
    required bool isDone,
    bool isActive = false,
  }) {
    Color bg;
    Color fg;
    Color border;

    if (isActive) {
      bg = AppColors.yellow;
      fg = AppColors.text;
      border = AppColors.yellowDark;
    } else if (isDone) {
      bg = AppColors.success;
      fg = Colors.white;
      border = AppColors.success;
    } else {
      bg = Colors.white;
      fg = AppColors.textLight;
      border = AppColors.border;
    }

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 24,
          height: 24,
          decoration: BoxDecoration(
            color: bg,
            shape: BoxShape.circle,
            border: Border.all(color: border, width: 1.5),
          ),
          child: Center(
            child: Text(
              icon,
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w900,
                color: fg,
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
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: isDone || isActive ? AppColors.text : AppColors.textLight,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                desc,
                style: const TextStyle(fontSize: 10, color: AppColors.textMuted),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildTrackerLine({required bool isDone}) {
    return Container(
      width: 2,
      height: 18,
      margin: const EdgeInsets.only(left: 11),
      color: isDone ? AppColors.success : AppColors.border,
    );
  }

  Widget _buildItemRow(String emoji, String title, String sub, String price) {
    return Row(
      children: [
        Container(
          width: 34,
          height: 34,
          decoration: BoxDecoration(
            color: const Color(0xFFF3F4F6),
            borderRadius: BorderRadius.circular(6),
          ),
          child: Center(
            child: Text(emoji, style: const TextStyle(fontSize: 18)),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
              ),
              Text(
                sub,
                style: const TextStyle(fontSize: 9, color: AppColors.textLight),
              ),
            ],
          ),
        ),
        Text(
          price,
          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.accent),
        ),
      ],
    );
  }

  Widget _buildPriceRow(String label, String value, {bool isGreen = false, bool isBold = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: isBold ? 12 : 11,
            fontWeight: isBold ? FontWeight.w800 : FontWeight.w500,
            color: isBold ? AppColors.text : AppColors.textMuted,
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: isBold ? 14 : 11,
            fontWeight: isBold ? FontWeight.w900 : FontWeight.w700,
            color: isGreen
                ? AppColors.success
                : isBold
                    ? AppColors.accent
                    : AppColors.text,
          ),
        ),
      ],
    );
  }
}
