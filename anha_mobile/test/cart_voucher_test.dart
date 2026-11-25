import 'package:flutter_test/flutter_test.dart';
import 'package:anha_mobile/providers/cart_provider.dart';

void main() {
  group('CartProvider Promo/Voucher Validation Tests', () {
    test('applyPromo with empty string returns error message and clears discount', () async {
      final cart = CartProvider();
      final error = await cart.applyPromo('   ');
      expect(error, contains('tidak boleh kosong'));
      expect(cart.promoCode, isNull);
      expect(cart.discount, 0);
    });

    test('removePromo resets promoCode and discount to 0', () {
      final cart = CartProvider();
      cart.removePromo();
      expect(cart.promoCode, isNull);
      expect(cart.discount, 0);
    });

    test('clearCartLocal resets items, discount, promoCode and estimatedPoints', () {
      final cart = CartProvider();
      cart.clearCartLocal();
      expect(cart.items.isEmpty, isTrue);
      expect(cart.discount, 0);
      expect(cart.promoCode, isNull);
      expect(cart.estimatedPoints, 0);
    });

    test('syncShippingConfig updates shippingEnabled and shippingCost dynamically from server', () {
      final cart = CartProvider();
      expect(cart.shippingEnabled, isFalse);
      expect(cart.shippingCost, 0);

      cart.syncShippingConfig({'shipping_enabled': true, 'shipping_flat_rate': 15000});
      expect(cart.shippingEnabled, isTrue);
      expect(cart.shippingFlatRate, 15000);
      expect(cart.shippingCost, 15000);

      cart.syncShippingConfig({'shipping_enabled': '1', 'shipping_flat_rate': '30000'});
      expect(cart.shippingEnabled, isTrue);
      expect(cart.shippingCost, 30000);

      cart.syncShippingConfig({'shipping_enabled': false, 'shipping_flat_rate': 30000});
      expect(cart.shippingEnabled, isFalse);
      expect(cart.shippingCost, 0);
    });
  });
}

