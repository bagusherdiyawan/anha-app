import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../providers/address_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/loyalty_provider.dart';
import '../../providers/order_provider.dart';
import '../../widgets/custom_button.dart';
import '../profile/address_list_screen.dart';
import 'order_success_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final TextEditingController _promoController = TextEditingController();
  bool _isCheckingOut = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final cart = context.read<CartProvider>();
      if (cart.promoCode != null) {
        _promoController.text = cart.promoCode!;
      }
      cart.fetchCart();
      context.read<LoyaltyProvider>().fetchVouchers();
    });
  }

  @override
  void dispose() {
    _promoController.dispose();
    super.dispose();
  }

  Future<void> _handleUpdateQty(CartProvider cart, int itemId, int newQty) async {
    final error = await cart.updateQty(itemId, newQty);
    if (!mounted || error == null) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(error),
        backgroundColor: AppColors.danger,
      ),
    );
  }

  Future<void> _handleCheckout(
    CartProvider cart,
    AddressProvider addressProvider,
  ) async {
    if (_isCheckingOut) return;
    setState(() => _isCheckingOut = true);

    final messenger = ScaffoldMessenger.of(context);
    final nav = Navigator.of(context);
    final orderProvider = context.read<OrderProvider>();

    try {
      await cart.waitForPendingMutations();
      if (!mounted) return;

      if (cart.items.isEmpty) {
        messenger.showSnackBar(
          const SnackBar(
            content: Text('Keranjang belanja kosong.'),
            backgroundColor: AppColors.danger,
          ),
        );
        return;
      }

      if (cart.minimumPurchase > 0 && cart.subtotal < cart.minimumPurchase) {
        messenger.showSnackBar(
          SnackBar(
            content: Text(
              'Minimal belanja ${CurrencyFormatter.format(cart.minimumPurchase)} untuk checkout.',
            ),
            backgroundColor: AppColors.danger,
          ),
        );
        return;
      }

      final activeAddress =
          addressProvider.selectedAddress ?? addressProvider.addresses.firstOrNull;
      if (activeAddress == null) {
        messenger.showSnackBar(
          const SnackBar(
            content: Text('Silakan tambahkan atau pilih alamat pengiriman terlebih dahulu.'),
            backgroundColor: AppColors.danger,
          ),
        );
        nav.push(
          MaterialPageRoute(builder: (_) => const AddressListScreen()),
        );
        return;
      }

      final placed = await orderProvider.placeOrder(
        addressId: activeAddress.id,
        voucherCode: cart.promoCode,
      );

      if (!mounted) return;

      if (placed != null) {
        final earnedPoints =
            placed.estimatedPoints > 0 ? placed.estimatedPoints : cart.estimatedPoints;
        final totalAmount = placed.total > 0 ? placed.total : cart.grandTotal;

        cart.clearCartLocal();
        nav.pushReplacement(
          MaterialPageRoute(
            builder: (_) => OrderSuccessScreen(
              orderId: placed.id,
              invoiceNumber: placed.invoice,
              earnedPoints: earnedPoints,
              total: totalAmount,
            ),
          ),
        );
      } else {
        messenger.showSnackBar(
          SnackBar(
            content: Text(
              orderProvider.errorMessage ??
                  'Checkout gagal. Periksa minimal belanja atau ketersediaan stok.',
            ),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isCheckingOut = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final addressProvider = context.watch<AddressProvider>();
    final selectedAddress = addressProvider.selectedAddress;

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
            Text('⚡', style: TextStyle(fontSize: 16)),
            SizedBox(width: 6),
            Text(
              'Keranjang Belanja',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: AppColors.primary,
              ),
            ),
          ],
        ),
      ),
      body: (cart.isLoading && cart.items.isEmpty)
          ? const Center(child: CircularProgressIndicator())
          : cart.items.isEmpty
              ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('🛒', style: TextStyle(fontSize: 48)),
                  const SizedBox(height: 12),
                  const Text(
                    'Keranjang Belanja Kosong',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      color: AppColors.text,
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'Yuk, cari perlengkapan listrik yang Anda butuhkan!',
                    style: TextStyle(fontSize: 12, color: AppColors.textMuted),
                  ),
                  const SizedBox(height: 20),
                  CustomButton(
                    width: 180,
                    text: 'Belanja Sekarang',
                    variant: ButtonVariant.yellow,
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Alamat Pengiriman Card (card-blue from wireframe)
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.primarySoft,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFBFDBFE)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 32,
                              height: 32,
                              decoration: BoxDecoration(
                                color: AppColors.primary,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Center(
                                child: Text('📍', style: TextStyle(fontSize: 16)),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Alamat Pengiriman',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                      color: AppColors.text,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    selectedAddress != null
                                        ? '${selectedAddress.recipientName} - ${selectedAddress.fullAddress}'
                                        : 'Belum ada alamat pengiriman. Silakan pilih atau tambah alamat.',
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontSize: 10,
                                      color: AppColors.textMuted,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              selectedAddress?.label.toUpperCase() ?? 'BELUM ADA ALAMAT',
                              style: const TextStyle(
                                fontSize: 9,
                                fontWeight: FontWeight.w800,
                                color: AppColors.textLight,
                              ),
                            ),
                            InkWell(
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => const AddressListScreen()),
                                );
                              },
                              child: Text(
                                selectedAddress != null ? 'Ubah Alamat' : 'Pilih/Tambah Alamat',
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.primary,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Items Header
                  Text(
                    'ITEMS (${cart.itemCount})',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: AppColors.text,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 10),

                  // Cart Items List
                  ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: cart.items.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 10),
                    itemBuilder: (context, index) {
                      final item = cart.items[index];
                      return Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              width: 52,
                              height: 52,
                              decoration: BoxDecoration(
                                color: const Color(0xFFF3F4F6),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Center(
                                child: Text('💡', style: TextStyle(fontSize: 24)),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item.barang?.namaBarang ?? 'Produk Listrik ANHA',
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w700,
                                      color: AppColors.text,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    item.barang?.kodeBarang ?? 'Original Part',
                                    style: const TextStyle(
                                      fontSize: 9,
                                      color: AppColors.textLight,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        CurrencyFormatter.format(item.harga),
                                        style: const TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.w900,
                                          color: AppColors.accent,
                                        ),
                                      ),
                                      // Counter
                                      Container(
                                        decoration: BoxDecoration(
                                          border: Border.all(color: AppColors.border),
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Row(
                                          children: [
                                            InkWell(
                                              onTap: (cart.isMutating || _isCheckingOut)
                                                  ? null
                                                  : () => _handleUpdateQty(cart, item.id, item.qty - 1),
                                              child: Container(
                                                width: 26,
                                                height: 26,
                                                alignment: Alignment.center,
                                                child: const Icon(Icons.remove, size: 14),
                                              ),
                                            ),
                                            Container(
                                              width: 30,
                                              alignment: Alignment.center,
                                              child: Text(
                                                item.qty.toString(),
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                  fontWeight: FontWeight.w800,
                                                ),
                                              ),
                                            ),
                                            InkWell(
                                              onTap: (cart.isMutating || _isCheckingOut)
                                                  ? null
                                                  : () => _handleUpdateQty(cart, item.id, item.qty + 1),
                                              child: Container(
                                                width: 26,
                                                height: 26,
                                                decoration: const BoxDecoration(
                                                  color: AppColors.primary,
                                                  borderRadius: BorderRadius.horizontal(right: Radius.circular(5)),
                                                ),
                                                alignment: Alignment.center,
                                                child: const Icon(Icons.add, size: 14, color: Colors.white),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 14),

                  // Shipping Info Card (synced from Web Admin settings)
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Row(
                            children: [
                              const Text('🚚', style: TextStyle(fontSize: 20)),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Pengiriman COD',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w800,
                                        color: AppColors.text,
                                      ),
                                    ),
                                    Text(
                                      cart.shippingCost > 0
                                          ? 'Tarif Ongkir Flat (${CurrencyFormatter.format(cart.shippingCost)})'
                                          : 'Gratis Ongkir (Rp 0)',
                                      style: const TextStyle(
                                        fontSize: 10,
                                        color: AppColors.textMuted,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: cart.shippingCost > 0 ? AppColors.primarySoft : AppColors.successSoft,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            cart.shippingCost > 0 ? 'ONGKIR FLAT' : 'GRATIS',
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w800,
                              color: cart.shippingCost > 0 ? AppColors.primary : AppColors.success,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Minimum purchase warning banner
                  if (cart.minimumPurchase > 0 && cart.subtotal < cart.minimumPurchase) ...[
                    Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.dangerSoft,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFFFECACA)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.warning_amber_rounded, color: AppColors.danger, size: 20),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Minimal belanja ${CurrencyFormatter.format(cart.minimumPurchase)} untuk checkout. Kurang ${CurrencyFormatter.format((cart.minimumPurchase - cart.subtotal).clamp(0, 999999999))} lagi.',
                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.danger),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],

                  // Promo Code Input
                  Row(
                    children: [
                      Expanded(
                        child: Container(
                          height: 42,
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: TextField(
                            controller: _promoController,
                            style: const TextStyle(fontSize: 12),
                            decoration: const InputDecoration(
                              hintText: 'Kode Voucher (contoh: VCH-...)',
                              hintStyle: TextStyle(fontSize: 11, color: AppColors.textLight),
                              border: InputBorder.none,
                              isDense: true,
                              contentPadding: EdgeInsets.symmetric(vertical: 12),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      CustomButton(
                        width: 90,
                        height: 42,
                        text: 'Pakai',
                        variant: ButtonVariant.yellow,
                        onPressed: () async {
                          final code = _promoController.text.trim();
                          if (code.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('Masukkan kode voucher terlebih dahulu.'),
                                backgroundColor: AppColors.danger,
                              ),
                            );
                            return;
                          }
                          FocusScope.of(context).unfocus();
                          final error = await cart.applyPromo(code);
                          if (!context.mounted) return;
                          if (error != null) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(error),
                                backgroundColor: AppColors.danger,
                              ),
                            );
                          } else {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('Voucher $code berhasil dipakai!'),
                                backgroundColor: AppColors.success,
                              ),
                            );
                          }
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),

                  // Voucher Picker Trigger
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      if (cart.promoCode != null)
                        Chip(
                          backgroundColor: AppColors.yellowSoft,
                          label: Text(
                            'Voucher: ${cart.promoCode}',
                            style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppColors.yellowDark),
                          ),
                          deleteIcon: const Icon(Icons.close, size: 14, color: AppColors.yellowDark),
                          onDeleted: () {
                            _promoController.clear();
                            cart.removePromo();
                          },
                        )
                      else
                        const SizedBox(),
                      InkWell(
                        onTap: () => _showVoucherPickerModal(context),
                        child: const Row(
                          children: [
                            Text('🎟️', style: TextStyle(fontSize: 12)),
                            SizedBox(width: 4),
                            Text(
                              'Pilih dari Voucher Saya ›',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: AppColors.primary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Electric Point Estimate Banner (wireframe yellow banner)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: AppColors.yellowSoft,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: AppColors.yellowBorder),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.primary,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Row(
                            children: [
                              const Text('⚡', style: TextStyle(fontSize: 10)),
                              const SizedBox(width: 4),
                              Text(
                                '+${cart.estimatedPoints} POIN LISTRIK',
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.yellow,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const Text(
                          'Bisa ditukar hadiah di toko',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: AppColors.yellowDark,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Total Breakdown Card
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Column(
                      children: [
                        _buildSummaryRow('Subtotal Produk', CurrencyFormatter.format(cart.subtotal)),
                        const SizedBox(height: 6),
                        _buildSummaryRow('Ongkos Kirim', CurrencyFormatter.format(cart.shippingCost)),
                        if (cart.discount > 0) ...[
                          const SizedBox(height: 6),
                          _buildSummaryRow('Diskon Voucher', '-${CurrencyFormatter.format(cart.discount)}', isDiscount: true),
                        ],
                        const Divider(height: 18, color: AppColors.border),
                        _buildSummaryRow(
                          'Total Pembayaran (COD)',
                          CurrencyFormatter.format(cart.grandTotal),
                          isTotal: true,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
      bottomNavigationBar: cart.items.isEmpty
          ? null
          : InkWell(
              onTap: (_isCheckingOut || cart.isBusy)
                  ? null
                  : () => _handleCheckout(cart, addressProvider),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 16),
                color: (_isCheckingOut || cart.isBusy || !cart.isEligibleCheckout)
                    ? AppColors.textMuted
                    : AppColors.primary,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text('💳', style: TextStyle(fontSize: 18)),
                    const SizedBox(width: 8),
                    Text(
                      _isCheckingOut
                          ? 'Memproses Pesanan...'
                          : cart.isBusy
                              ? 'Menyinkronkan Keranjang...'
                              : cart.isEligibleCheckout
                                  ? 'Bayar COD — ${CurrencyFormatter.format(cart.grandTotal)} ›'
                                  : 'Belum Memenuhi Min. Belanja (${CurrencyFormatter.format(cart.minimumPurchase)})',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  void _showVoucherPickerModal(BuildContext context) {
    final loyalty = context.read<LoyaltyProvider>();
    final cart = context.read<CartProvider>();
    final vouchers = loyalty.vouchers;

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) {
        return Container(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Pilih Voucher Belanja',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.text),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              if (vouchers.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 24),
                  child: Center(
                    child: Text(
                      'Tidak ada voucher yang dapat digunakan saat ini.',
                      style: TextStyle(fontSize: 11, color: AppColors.textMuted),
                    ),
                  ),
                )
              else
                Flexible(
                  child: ListView.separated(
                    shrinkWrap: true,
                    itemCount: vouchers.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (_, i) {
                      final v = vouchers[i];
                      final isPercent = v.discountType == 'percent';
                      final discountStr = isPercent
                          ? '${v.discountValue.toInt()}%'
                          : CurrencyFormatter.format(v.discountValue.toInt());
                      final isMinMet = cart.subtotal >= v.minPurchase;

                      return Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isMinMet ? Colors.white : const Color(0xFFF3F4F6),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Row(
                          children: [
                            Text(isPercent ? '🏷️' : '⚡', style: const TextStyle(fontSize: 22)),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Potongan $discountStr (${v.code})',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w800,
                                      color: isMinMet ? AppColors.text : AppColors.textLight,
                                    ),
                                  ),
                                  Text(
                                    'Min. belanja ${CurrencyFormatter.format(v.minPurchase.toInt())}',
                                    style: const TextStyle(fontSize: 10, color: AppColors.textMuted),
                                  ),
                                ],
                              ),
                            ),
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: isMinMet ? AppColors.yellow : Colors.grey.shade300,
                                elevation: 0,
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              ),
                              onPressed: isMinMet
                                  ? () async {
                                      Navigator.pop(ctx);
                                      _promoController.text = v.code;
                                      final error = await cart.applyPromo(
                                        v.code,
                                        explicitDiscount: isPercent ? null : v.discountValue.toInt(),
                                      );
                                      if (!context.mounted) return;
                                      if (error != null) {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          SnackBar(
                                            content: Text(error),
                                            backgroundColor: AppColors.danger,
                                          ),
                                        );
                                      } else {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          SnackBar(
                                            content: Text('Voucher ${v.code} diterapkan!'),
                                            backgroundColor: AppColors.success,
                                          ),
                                        );
                                      }
                                    }
                                  : null,
                              child: Text(
                                isMinMet ? 'Gunakan' : 'Min Belum Cukup',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  color: isMinMet ? AppColors.text : AppColors.textLight,
                                ),
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSummaryRow(String label, String value, {bool isDiscount = false, bool isTotal = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: isTotal ? 12 : 11,
            fontWeight: isTotal ? FontWeight.w800 : FontWeight.w500,
            color: isTotal ? AppColors.text : AppColors.textMuted,
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: isTotal ? 14 : 11,
            fontWeight: isTotal ? FontWeight.w900 : FontWeight.w700,
            color: isDiscount
                ? AppColors.danger
                : isTotal
                    ? AppColors.accent
                    : AppColors.text,
          ),
        ),
      ],
    );
  }
}
