import 'package:flutter_test/flutter_test.dart';
import 'package:anha_mobile/models/address_model.dart';
import 'package:anha_mobile/models/cart_model.dart';
import 'package:anha_mobile/models/category_model.dart';
import 'package:anha_mobile/models/feedback_model.dart';
import 'package:anha_mobile/models/loyalty_model.dart';
import 'package:anha_mobile/models/notification_model.dart';
import 'package:anha_mobile/models/order_model.dart';
import 'package:anha_mobile/models/product_model.dart';
import 'package:anha_mobile/models/rekening_model.dart';
import 'package:anha_mobile/models/user_model.dart';

void main() {
  group('Backend Payload & Schema Alignment Tests', () {
    test('AddressModel.toJson() matches AlamatController.php store/update rules', () {
      final address = AddressModel(
        id: 1,
        label: 'Kantor Proyek',
        recipientName: 'Budi Darmawan',
        phone: '081298765432',
        address: 'Jl. Industri No. 10',
        district: 'Cikarang',
        city: 'Bekasi',
        province: 'Jawa Barat',
        postalCode: '17530',
        isDefault: true,
        latitude: -6.297491,
        longitude: 107.135248,
      );

      final json = address.toJson();

      // Required by AlamatController@store
      expect(json['label'], 'Kantor Proyek');
      expect(json['recipient_name'], 'Budi Darmawan');
      expect(json['phone'], '081298765432');
      expect(json['address'], 'Jl. Industri No. 10');
      expect(json['city'], 'Bekasi');
      expect(json['province'], 'Jawa Barat');
      expect(json['postal_code'], '17530');
      expect(json['default'], 1);
      expect(json['latitude'], -6.297491);
      expect(json['longitude'], 107.135248);
    });

    test('ProductModel.fromJson() correctly parses backend BarangFrontController payload', () {
      final beBarangJson = {
        'id': 101,
        'code': 'KBL001',
        'name': 'Kabel NYM 3x2.5mm',
        'slug': 'kabel-nym-101',
        'satuan': 'meter',
        'harga': 45000,
        'retail_price': 45000,
        'quality_rating': 5,
        'stock': 150,
        'is_available': 1,
        'primary_image_url': 'photos_barang/nym.jpg',
        'category': {'id': 2, 'name': 'Kabel Listrik'},
      };

      final product = ProductModel.fromJson(beBarangJson);
      expect(product.id, 101);
      expect(product.namaBarang, 'Kabel NYM 3x2.5mm');
      expect(product.kodeBarang, 'KBL001');
      expect(product.harga, 45000);
      expect(product.rating, 5.0);
      expect(product.stock, 150);
      expect(product.isAvailable, true);
      expect(product.foto, 'photos_barang/nym.jpg');
      expect(product.satuan, 'meter');
      expect(product.categoryName, 'Kabel Listrik');
    });

    test('ProductModel.fromJson() parses detail images objects, deduplicates URLs, and resolves unit names', () {
      final detailJson = {
        'id': 24,
        'code': '24',
        'name': 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
        'satuan': '1',
        'satuan_nama': 'Roll',
        'harga': 495000,
        'stock': 8,
        'is_available': true,
        'primary_image_url': 'photos_barang/eterna1.webp',
        'images': [
          {'foto_url': 'https://api.anhagroup.com/storage/photos_barang/eterna1.webp'},
          {'foto_url': 'https://api.anhagroup.com/storage/photos_barang/eterna2.webp'},
        ],
      };

      final detail = ProductModel.fromJson(detailJson);
      expect(detail.satuan, 'Roll');
      expect(detail.fullPhotoUrls, [
        'https://api.anhagroup.com/storage/photos_barang/eterna1.webp',
        'https://api.anhagroup.com/storage/photos_barang/eterna2.webp',
      ]);

      // Rejects bare numeric foreign-key satuan when unit name is absent
      final legacySearchItem = ProductModel.fromJson({
        'id': 24,
        'name': 'Kabel Eterna',
        'satuan': '1',
        'stock': 8,
        'harga': 495000,
        'primary_image_url': 'photos_barang/eterna1.webp',
      });
      expect(legacySearchItem.satuan, 'pcs');

      // Merges multi-photo gallery from detail payload over single-photo list item
      final merged = legacySearchItem.mergeWithDetail(detail);
      expect(merged.fullPhotoUrls.length, 2);
      expect(merged.satuan, 'Roll');
    });

    test('CartItemModel.fromJson() parses KeranjangController item transform structure', () {
      final beCartItem = {
        'id': 5,
        'product_id': 101,
        'product_name': 'Kabel NYM',
        'unit_price': 45000,
        'quantity': 3,
        'subtotal': 135000,
        'stock': 150,
        'is_available': true,
      };

      final cartItem = CartItemModel.fromJson(beCartItem);
      expect(cartItem.id, 5);
      expect(cartItem.barangId, 101);
      expect(cartItem.harga, 45000);
      expect(cartItem.qty, 3);
      expect(cartItem.subtotal, 135000);
    });

    test('OrderModel.fromJson() parses OrderController backend response with orderItems & alamat_pengiriman', () {
      final beOrderJson = {
        'id': 88,
        'invoice': 'INV-1790599689',
        'total': 270000,
        'shipping_cost': '15000.00',
        'status': 'delivering',
        'estimated_points': 6,
        'alamat_pengiriman': {
          'id': 1,
          'label': 'Rumah',
          'recipient_name': 'Budi Darmawan',
          'phone': '081298765432',
          'address': 'Jl. Elektro No. 42',
          'city': 'Tangerang',
          'province': 'Banten',
          'postal_code': '15810',
          'latitude': -6.240582,
          'longitude': 106.629168,
        },
        'orderItems': [
          {
            'id': 1,
            'barang_id': 101,
            'qty': 2,
            'harga': 135000,
            'barang': {
              'id': 101,
              'nama_barang': 'MCB 16A',
              'satuan': 'pcs',
              'rating': 5,
            }
          }
        ]
      };

      final order = OrderModel.fromJson(beOrderJson);
      expect(order.id, 88);
      expect(order.invoice, 'INV-1790599689');
      expect(order.total, 270000);
      expect(order.shippingCost, 15000);
      expect(order.status, 'delivering');
      expect(order.estimatedPoints, 6);
      expect(order.alamatPengiriman?.recipientName, 'Budi Darmawan');
      expect(order.items.length, 1);
      expect(order.items.first.barang?.namaBarang, 'MCB 16A');
      expect(order.items.first.barang?.harga, 135000);

      // Enriched barang payload from OrderController takes precedence over historical order item harga
      final enrichedOrder = OrderModel.fromJson({
        'id': 90,
        'total': 990000,
        'orderItems': [
          {
            'id': 2,
            'barang_id': 24,
            'qty': 2,
            'harga': 450000,
            'barang': {
              'id': 24,
              'kode_barang': 'KBL-024',
              'nama_barang': 'Kabel Eterna NYM 2x1.5mm (50 Meter)',
              'satuan': 'Roll',
              'stock': 8,
              'is_available': true,
              'harga': 495000,
              'price': 495000,
            },
          },
        ],
      });
      expect(enrichedOrder.items.first.barang?.kodeBarang, 'KBL-024');
      expect(enrichedOrder.items.first.barang?.harga, 495000);
      expect(enrichedOrder.items.first.barang?.inStock, isTrue);

      // Also parses estimasi_poin or points aliases
      final altOrder = OrderModel.fromJson({'id': 89, 'estimasi_poin': 12, 'total': 493000});
      expect(altOrder.estimatedPoints, 12);
      expect(altOrder.total, 493000);
    });

    test('LoyaltySummaryModel.fromJson() parses LoyaltyController summary response', () {
      final beSummary = {
        'points_balance': 55,
        'total_earned': 58,
        'total_spent': 3,
        'expired': 0,
        'points_value': 27500,
        'points_value_formatted': 'Rp 27.500',
      };

      final summary = LoyaltySummaryModel.fromJson(beSummary);
      expect(summary.balance, 55);
      expect(summary.totalEarned, 58);
      expect(summary.totalSpent, 3);
      expect(summary.expired, 0);
      expect(summary.rupiahValue, 27500);
    });

    test('NotificationModel.fromJson() parses PushNotification backend schema', () {
      final beNotifJson = {
        'id': 12,
        'user_id': 1,
        'category': 'delivery',
        'title': 'Kurir Sedang Berangkat',
        'body': 'Pesanan Anda sedang diantar oleh kurir.',
        'is_read': 0,
        'created_at': '2026-09-28T14:00:00Z',
      };

      final notif = NotificationModel.fromJson(beNotifJson);
      expect(notif.id, 12);
      expect(notif.title, 'Kurir Sedang Berangkat');
      expect(notif.isRead, false);
      expect(notif.iconEmoji, '🚚');
    });

    test('UserModel.fromJson() parses AuthController login & profile response', () {
      final beUserJson = {
        'id': 1,
        'type': 'grosir',
        'name': 'PT Sinar Mandiri',
        'email': '8899', // 4-digit generated access code
        'phone': '081234567890',
        'subscription_number': 'SUB-001',
        'points': 500,
        'is_active': 1,
      };

      final user = UserModel.fromJson(beUserJson);
      expect(user.id, 1);
      expect(user.role, 'grosir');
      expect(user.isB2B, true);
      expect(user.points, 500);
      expect(user.isActive, true);
    });

    test('CategoryModel.fromJson() parses Kategori_barangFrontController landing schema and fullPhotoUrl', () {
      final beCatJson = {
        'id': 3,
        'nama_kategori': 'Kabel & Kawat',
        'icon': 'photos_kategori/kabel.webp',
      };

      final cat = CategoryModel.fromJson(beCatJson);
      expect(cat.id, 3);
      expect(cat.nama, 'Kabel & Kawat');
      expect(cat.fullPhotoUrl, 'https://api.anhagroup.com/storage/photos_kategori/kabel.webp');

      final fullUrlCat = CategoryModel.fromJson({
        'id': 4,
        'name': 'Lampu LED',
        'icon': 'https://api.anhagroup.com/storage/photos_kategori/lampu.webp',
      });
      expect(fullUrlCat.fullPhotoUrl, 'https://api.anhagroup.com/storage/photos_kategori/lampu.webp');

      final emptyCat = CategoryModel.fromJson({'id': 5, 'name': 'Kosong', 'icon': ''});
      expect(emptyCat.fullPhotoUrl, isNull);
    });

    test('FeedbackModel.fromJson() parses FeedbackController store & index schema', () {
      final beFeedbackJson = {
        'id': 5,
        'user_id': 1,
        'category': 'Pengiriman',
        'message': 'Pengiriman sangat cepat dan kurir ramah.',
        'status': 'replied',
        'reply': 'Terima kasih atas masukannya kak!',
        'created_at': '2026-09-28 10:00:00',
      };

      final feedback = FeedbackModel.fromJson(beFeedbackJson);
      expect(feedback.id, 5);
      expect(feedback.category, 'Pengiriman');
      expect(feedback.status, 'replied');
      expect(feedback.reply, isNotNull);
    });

    test('VoucherModel.fromJson() parses LoyaltyController vouchers response', () {
      final beVoucherJson = {
        'id': 7,
        'code': 'ANHAHEMAT',
        'discount_value': '25000.00',
        'discount_type': 'fixed',
        'min_purchase': '100000.00',
        'is_used': 0,
      };

      final voucher = VoucherModel.fromJson(beVoucherJson);
      expect(voucher.id, 7);
      expect(voucher.code, 'ANHAHEMAT');
      expect(voucher.discountValue, 25000.0);
      expect(voucher.minPurchase, 100000.0);
      expect(voucher.isUsed, false);
    });

    test('RekeningModel.fromJson() parses Rekening_tokoFrontController schema', () {
      final beRekeningJson = {
        'id': 1,
        'nama': 'Bank Central Asia (BCA)',
        'nomor': '8040189914',
        'type': 'bank',
        'foto': 'rekenings/bca.png',
        'keterangan': 'Transfer sesuai total belanja',
      };

      final rek = RekeningModel.fromJson(beRekeningJson);
      expect(rek.id, 1);
      expect(rek.nama, 'Bank Central Asia (BCA)');
      expect(rek.nomor, '8040189914');
      expect(rek.type, 'bank');
      expect(rek.photoUrl, contains('rekenings/bca.png'));
    });

    test('RewardModel.fromJson() parses RewardResource image_url, backend types, and unlimited stock (-1)', () {
      final boxMcb = RewardModel.fromJson({
        'id': 3,
        'type': 'physical_item',
        'name': 'Box MCB',
        'image_url': 'https://api.anhagroup.com/storage/photos_barang/mcb_box.webp',
        'points_required': 40,
        'stock': 5,
        'is_active': true,
      });
      expect(boxMcb.name, 'Box MCB');
      expect(boxMcb.normalizedCategory, 'physical');
      expect(boxMcb.image, 'https://api.anhagroup.com/storage/photos_barang/mcb_box.webp');
      expect(boxMcb.inStock, isTrue);
      expect(boxMcb.isUnlimitedStock, isFalse);

      final voucherReward = RewardModel.fromJson({
        'id': 4,
        'type': 'voucher_discount',
        'name': 'Voucher Diskon',
        'image_url': null,
        'points_required': 100,
        'stock': -1,
        'is_active': 1,
      });
      expect(voucherReward.normalizedCategory, 'voucher');
      expect(voucherReward.isUnlimitedStock, isTrue);
      expect(voucherReward.inStock, isTrue);

      final directReward = RewardModel.fromJson({
        'id': 5,
        'type': 'direct_discount',
        'name': 'Potongan Langsung',
        'points_required': 200,
        'stock': 0,
        'is_active': true,
      });
      expect(directReward.normalizedCategory, 'discount');
      expect(directReward.inStock, isFalse);
    });
  });
}
