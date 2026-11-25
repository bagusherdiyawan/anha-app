import 'package:intl/intl.dart';

class CurrencyFormatter {
  static final NumberFormat _currencyFormat = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  static String format(dynamic value) {
    if (value == null) return 'Rp 0';
    if (value is num) {
      return _currencyFormat.format(value);
    }
    if (value is String) {
      final parsed = num.tryParse(value);
      if (parsed != null) {
        return _currencyFormat.format(parsed);
      }
    }
    return 'Rp 0';
  }

  static String formatRupiah(dynamic value) => format(value);

  static String formatNumber(dynamic value) {
    if (value == null) return '0';
    final formatter = NumberFormat('#,###', 'id_ID');
    if (value is num) {
      return formatter.format(value);
    }
    if (value is String) {
      final parsed = num.tryParse(value);
      if (parsed != null) {
        return formatter.format(parsed);
      }
    }
    return value.toString();
  }
}
