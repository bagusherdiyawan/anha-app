class ApiEndpoints {
  // Base URLs
  static const String baseUrl = 'https://api.anhagroup.com/api';
  static const String storageUrl = 'https://api.anhagroup.com/storage/';

  /// Normalizes storage paths or legacy host URLs into a fetchable network URL.
  static String? normalizeStorageUrl(String? rawPath) {
    final raw = rawPath?.trim();
    if (raw == null || raw.isEmpty || raw.toLowerCase() == 'null') return null;
    if (raw.startsWith('http://') || raw.startsWith('https://')) {
      return raw.replaceFirst('://anha.inforkampus.com', '://api.anhagroup.com');
    }
    final cleaned = raw.startsWith('/') ? raw.substring(1) : raw;
    if (cleaned.startsWith('storage/')) {
      return '$storageUrl${cleaned.substring('storage/'.length)}';
    }
    return '$storageUrl$cleaned';
  }

  // Auth & General
  static const String login = '/v1/login';
  static const String logout = '/v1/logout';
  static const String contactInfo = '/v1/contact-info';
  static const String updateDeviceToken = '/v1/update-token';

  // Profile
  static const String profile = '/v1/profil';
  static const String updatePhoto = '/v1/profil-ganti-foto';

  // Address
  static const String addresses = '/v1/alamat-pengiriman';
  static const String setDefaultAddress = '/v1/alamat-pengiriman-default';

  // Banners & Categories
  static const String banners = '/v1/banner-all';
  static const String categoriesLanding = '/v1/kategori-barang-landing';
  static const String categoriesAll = '/v1/kategori-barang-all';

  // Products
  static const String productsLanding = '/v1/barang-user-landing';
  static const String productsByCategory = '/v1/barang-user-kategori';
  static const String productSearch = '/v1/barang-user-search';
  static const String productBarcode = '/v1/barang-user-barcode';
  static const String productDetail = '/v1/barang-user';

  // Cart & Checkout
  static const String cart = '/v1/keranjang';
  static const String cartDeleteAll = '/v1/delete-all';
  static const String checkoutPre = '/v1/checkout-pre';
  static const String checkout = '/v1/checkout';

  // Orders
  static const String orders = '/v1/order';
  static const String confirmReceived = '/confirm-received';
  static const String cancelOrder = '/cancel';

  // Loyalty & Rewards
  static const String loyaltySummary = '/v1/loyalty/summary';
  static const String loyaltyTransactions = '/v1/loyalty/transactions';
  static const String loyaltyRewards = '/v1/loyalty/rewards';
  static const String loyaltyRedeem = '/v1/loyalty/redeem';
  static const String loyaltyRedemptions = '/v1/loyalty/redemptions';
  static const String loyaltyVouchers = '/v1/loyalty/vouchers';

  // Feedbacks (Kritik & Saran)
  static const String feedbacks = '/v1/feedbacks';

  // Notifications
  static const String notifications = '/v1/notifications';
  static const String unreadNotificationCount = '/v1/notifications/unread-count';
  static const String markNotificationRead = '/v1/notifications';
  static const String markAllNotificationsRead = '/v1/notifications/read-all';

  // Store & Payment Methods
  static const String storeAccounts = '/v1/rekening-dan-cara-bayar';
  static const String storeAccountsSimple = '/v1/rekening';

  // Admin Features (Mobile)
  static const String adminBarangs = '/v1/barangs';
}
