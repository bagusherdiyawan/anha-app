<?php 

use App\Http\Controllers\Api\V1\admin\AlamatAdminController;
use App\Http\Controllers\Api\V1\admin\BannerController;
use App\Http\Controllers\Api\V1\admin\BarangController;
use App\Http\Controllers\Api\V1\admin\Biaya_antar_batasController;
use App\Http\Controllers\Api\V1\admin\Biaya_antarController;
use App\Http\Controllers\Api\V1\admin\DashboardController;
use App\Http\Controllers\Api\V1\admin\FeedbackAdminController;
use App\Http\Controllers\Api\V1\admin\GenerateKodeGrosirController;
use App\Http\Controllers\Api\V1\admin\Kategori_barangController;
use App\Http\Controllers\Api\V1\admin\OrderAdminController;
use App\Http\Controllers\Api\V1\admin\PaymentController;
use App\Http\Controllers\Api\V1\admin\PaymentStatusController;
use App\Http\Controllers\Api\V1\admin\PromoController;
use App\Http\Controllers\Api\V1\admin\Rekening_tokoController;
use App\Http\Controllers\Api\V1\admin\RewardAdminController;
use App\Http\Controllers\Api\V1\admin\RewardRedemptionAdminController;
use App\Http\Controllers\Api\V1\admin\ReportAdminController;
use App\Http\Controllers\Api\V1\admin\SettingAdminController;
use App\Http\Controllers\Api\V1\admin\SystemConfigAdminController;
use App\Http\Controllers\Api\V1\admin\TokoController;
use App\Http\Controllers\Api\V1\admin\UserController;
use App\Http\Controllers\Api\V1\admin\PushNotificationController;
use App\Http\Controllers\Api\V1\admin\SatuanBarangAdminController;

use App\Http\Controllers\Api\V1\general\AuthController;
use App\Http\Controllers\Api\V1\user\AlamatController;
use App\Http\Controllers\Api\V1\user\BannerFrontController;
use App\Http\Controllers\Api\V1\user\BarangFrontController;
use App\Http\Controllers\Api\V1\user\Biaya_antar_batasFrontController;
use App\Http\Controllers\Api\V1\user\Biaya_antarFrontController;
use App\Http\Controllers\Api\V1\user\CheckoutController;
use App\Http\Controllers\Api\V1\user\FeedbackController;
use App\Http\Controllers\Api\V1\user\Kategori_barangFrontController;
use App\Http\Controllers\Api\V1\user\KeranjangController;
use App\Http\Controllers\Api\V1\user\LoyaltyController;
use App\Http\Controllers\Api\V1\user\NotificationController;
use App\Http\Controllers\Api\V1\user\OrderController;
use App\Http\Controllers\Api\V1\user\PaymentFrontController;
use App\Http\Controllers\Api\V1\user\ProfilController;
use App\Http\Controllers\Api\V1\user\PromoFrontController;
use App\Http\Controllers\Api\V1\user\Rekening_tokoFrontController;
use App\Http\Middleware\CheckUserRoleAdmin;
use App\Http\Middleware\CheckUserRoleUser;
use Illuminate\Support\Facades\Route;

// GENERAL NO AUTH OK
Route::post('/v1/login', [AuthController::class, 'login']);
Route::post('/v1/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/v1/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/v1/contact-info', function () {
    $wa = \App\Models\SystemConfig::get('whatsapp_number', '6285169189914');
    $waClean = preg_replace('/[^0-9]/', '', (string)$wa);
    if (str_starts_with($waClean, '0')) {
        $waClean = '62' . substr($waClean, 1);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'whatsapp_number' => $waClean ?: '6285169189914',
            'whatsapp_template' => \App\Models\SystemConfig::get('whatsapp_template', 'Hallo Pelanggan ANHA Elektronik, saya ingin bertanya mengenai Produk?'),
            'phone_number' => \App\Models\SystemConfig::get('phone_number', '085791777377'),
            'email_contact' => \App\Models\SystemConfig::get('email_contact', 'info@anha-elektronik.com'),
        ]
    ]);
});

// GENERAL WITH AUTH OK
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/v1/logout', [AuthController::class, 'logout']);
    Route::post('/v1/logout-all-devices', [AuthController::class, 'logoutAllDevices']);
    Route::post('/v1/update-token', [AuthController::class, 'updateDeviceToken']);
});

// APK USER
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware(CheckUserRoleUser::class)->group(function () {

        // Profil OK
        Route::get('/v1/profil', [ProfilController::class, 'index']);
        Route::match(['put', 'post'], '/v1/profil/{id}', [ProfilController::class, 'ctrl_update']);
        Route::match(['put', 'post'], '/v1/profil-ganti-foto/{id?}', [ProfilController::class, 'ctrl_update_foto']);
        Route::match(['put', 'post'], '/v1/profil-ganto-foto/{id?}', [ProfilController::class, 'ctrl_update_foto']);
        Route::put('/v1/profil-ganti-password', [ProfilController::class, 'changePassword']);

        // Alamat OK
        Route::apiResource('/v1/alamat-pengiriman', AlamatController::class);
        Route::patch('/v1/alamat-pengiriman-default/{id}', [AlamatController::class, 'ctrl_setDefaultAlamat']);

        // Barang / Product
        Route::apiResource('/v1/barang-user', BarangFrontController::class);
        Route::get('/v1/barang-user-search', [BarangFrontController::class, 'search']);
        Route::get('/v1/barang-user-kategori/{id}', [BarangFrontController::class, 'showByKategoriId']);
        Route::get('/v1/barang-user-landing', [BarangFrontController::class, 'landing']);
        Route::get('/v1/barang-user-barcode/{code}', [BarangFrontController::class, 'showByBarcode']);

        Route::get('/v1/kategori-barang-all', [Kategori_barangFrontController::class, 'index']);
        Route::get('/v1/kategori-barang-landing', [Kategori_barangFrontController::class, 'landing']);

        Route::get('/v1/banner-all', [BannerFrontController::class, 'index']);

        Route::apiResource('/v1/keranjang', KeranjangController::class);
        Route::delete('/v1/delete-all', [KeranjangController::class, 'destroyAll']);

        Route::get('/v1/checkout-pre', [CheckoutController::class, 'ctrl_checkout_pre']);
        Route::post('/v1/checkout', [CheckoutController::class, 'ctrl_checkout']);

        Route::get('/v1/order', [OrderController::class, 'index']);
        Route::get('/v1/order/{id}', [OrderController::class, 'show']);
        Route::patch('/v1/order/{id}/confirm-received', [OrderController::class, 'confirmReceived']);
        Route::patch('/v1/order/{id}/cancel', [OrderController::class, 'cancel']);

        // Kritik & Saran OK
        Route::get('/v1/feedbacks', [FeedbackController::class, 'index']);
        Route::post('/v1/feedbacks', [FeedbackController::class, 'store']);
        Route::get('/v1/feedbacks/{feedback}', [FeedbackController::class, 'show']);


        Route::get('/v1/rekening', [Rekening_tokoFrontController::class, 'index']);
        Route::get('/v1/rekening-dan-cara-bayar', [Rekening_tokoFrontController::class, 'withCaraBayar']);

        // Loyalty
        Route::get('v1/loyalty/summary', [LoyaltyController::class, 'summary']);
        Route::get('v1/loyalty/transactions', [LoyaltyController::class, 'transactions']);
        Route::get('v1/loyalty/rewards', [LoyaltyController::class, 'rewards']);
        Route::post('v1/loyalty/redeem/{reward}', [LoyaltyController::class, 'redeem']);
        Route::get('v1/loyalty/redemptions', [LoyaltyController::class, 'redemptions']);
        Route::get('v1/loyalty/redemptions/{redemption}', [LoyaltyController::class, 'redemptionDetail']);
        Route::get('v1/loyalty/vouchers', [LoyaltyController::class, 'vouchers']);

        // Notifications OK
        Route::get('v1/notifications',[NotificationController::class, 'index']);
        Route::get('v1/notifications/unread-count',[NotificationController::class, 'unreadCount']);
        Route::get('v1/notifications/{id}',[NotificationController::class, 'show']);
        Route::patch('v1/notifications/{id}/read',[NotificationController::class, 'markAsRead']);
        Route::patch('v1/notifications/read-all',[NotificationController::class, 'markAllAsRead']);

        // Route::get('biaya-antar-all', [Biaya_antarFrontController::class, 'index']);
        // Route::get('batas-biaya-antar', [Biaya_antar_batasFrontController::class, 'index']);

        // Route::apiResource('payments', PaymentFrontController::class);
        // Route::get('payments-riwayat/{id?}', [PaymentFrontController::class, 'riwayatPembelian']);
        // Route::get('payments-riwayat-detail/{id?}', [PaymentFrontController::class, 'riwayatPembelianDetail']);
        // Route::put('payments-ubah-method/{id}', [PaymentFrontController::class, 'gantiMethodPembayaran']);
        // Route::put('payments-upload-buktibayar/{id}', [PaymentFrontController::class, 'uploadBuktiTransfer']);
        // Route::post('payments-gtoken-bearer', [PaymentFrontController::class, 'getBearer']);
        // Route::post('payments-kirim-notifikasi', [PaymentFrontController::class, 'kirimNotifikasi']);

        // Route::put('update-token/{id}', [UserController::class, 'updateTokenFbase']);
        // Route::get('promo-all', [PromoFrontController::class, 'index']);
    });
});

// CMS
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware(CheckUserRoleAdmin::class)->group(function () {

        // APK ADMIN - Riwayat pembayaran (dipindah ke dalam auth admin, sebelumnya publik)
        Route::get('admin-payments-riwayat', [PaymentFrontController::class, 'riwayatPembelianByAdmin']);
        Route::get('admin-payments-riwayat-detail/{id?}', [PaymentFrontController::class, 'riwayatPembelianDetailByAdmin']);

        // Dashboard
        Route::get('/v1/admin-dashboard', [DashboardController::class, 'index']);
     
        // Banner
        Route::apiResource('/v1/banners', BannerController::class);
     
        // Users
        Route::apiResource('/v1/users', UserController::class);
    
        // Push-notifications. OK
        Route::apiResource('/v1/push-notifications',PushNotificationController::class);
   
        // Admin alamat pengiriman
        Route::apiResource('/v1/admin-alamat-pengiriman', AlamatAdminController::class);

        // Satuan Barang OK
        Route::apiResource('/v1/admin-satuan-barang', SatuanBarangAdminController::class);

        // Barang OK
        Route::apiResource('/v1/barangs', BarangController::class);
        Route::patch('v1/barang-update-status/{id}', [BarangController::class, 'update_status']);
        Route::patch('v1/barang-update-availability/{id}', [BarangController::class, 'update_availability']);
        Route::get('/v1/barang-ritel', [BarangController::class, 'getDataRitel']);
        Route::get('/v1/barang-grosir', [BarangController::class, 'getDataGrosir']);

        // Kategori OK
        Route::apiResource('/v1/kategoribarangs', Kategori_barangController::class);

        // Generate kode grosir
        Route::apiResource('/v1/generate-kode-grosir', GenerateKodeGrosirController::class);
        Route::post('/v1/generate-kode-grosir-auto', [GenerateKodeGrosirController::class, 'generateKode']);

        // Kritik & Saran OK
        Route::apiResource('/v1/admin-feedbacks', FeedbackAdminController::class)->only(['index', 'show', 'destroy']);
        Route::post('/v1/admin-feedbacks/{id}/reply', [FeedbackAdminController::class, 'reply']);
        Route::patch('/v1/admin-feedbacks/{id}/status', [FeedbackAdminController::class, 'updateStatus']);
        Route::patch('/v1/admin-feedbacks/{id}/archive', [FeedbackAdminController::class, 'archive']);

        // Pesanan / Admin Order OK
        Route::get('/v1/admin-orders', [OrderAdminController::class, 'index']);
        Route::get('/v1/admin-orders/{id}', [OrderAdminController::class, 'show']);
        Route::patch('/v1/admin-orders/{id}/status', [OrderAdminController::class, 'updateStatus']);

        Route::apiResource('/v1/admin-rewards', RewardAdminController::class);
        Route::patch('/v1/admin-rewards/{id}/toggle', [RewardAdminController::class, 'toggleActive']);

        Route::get('/v1/admin-reward-redemptions', [RewardRedemptionAdminController::class, 'index']);
        Route::patch('/v1/admin-reward-redemptions/{id}/status', [RewardRedemptionAdminController::class, 'updateStatus']);

        // reports
        Route::get('/v1/admin-reports/sales', [ReportAdminController::class, 'sales']);
        Route::get('/v1/admin-reports/stock', [ReportAdminController::class, 'stock']);
        Route::get('/v1/admin-reports/customers', [ReportAdminController::class, 'customers']);
        Route::get('/v1/admin-reports/points', [ReportAdminController::class, 'points']);

        // system config
        Route::apiResource('/v1/admin-system-configs', SystemConfigAdminController::class);

        // update multiple config sekaligus
        Route::patch('/v1/admin-system-configs-bulk-update', [SystemConfigAdminController::class, 'bulkUpdate']);

        // clear cache
        Route::post('/v1/admin-system-configs/clear-cache', [SystemConfigAdminController::class, 'clearCache']);

        // admin management
        Route::get('/v1/admin-settings/admins', [SettingAdminController::class, 'admins']);
        Route::post('/v1/admin-settings/admins', [SettingAdminController::class, 'storeAdmin']);
        Route::patch('/v1/admin-settings/admins/{id}/toggle', [SettingAdminController::class, 'toggleAdmin']);
        Route::delete('/v1/admin-settings/admins/{id}', [SettingAdminController::class, 'deleteAdmin']);

        Route::apiResource('/v1/admin-rekening', Rekening_tokoController::class);
        Route::patch('/v1/admin-rekening/{id}/toggle', [Rekening_tokoController::class, 'toggleActive']);
        // Route::apiResource('promos', PromoController::class);

        // Route::get('payment-status', [PaymentStatusController::class, 'index']);
        // Route::get('payment-cekout', [PaymentController::class, 'cekout']);
        // Route::put('payment-ubah-status-bayar/{id}', [PaymentController::class, 'gantiStatusPembayaran']);
        // Route::apiResource('laporan-riwayat-pembayaran', PaymentController::class);
        Route::get('laporan-riwayat-pembayaran-detail/{id?}', [PaymentController::class, 'riwayatPembelianDetail']);
    });
});

// GET     /api/tokos           -> TokoController@index
// POST    /api/tokos           -> TokoController@store
// GET     /api/tokos/{toko}    -> TokoController@show
// PUT/PATCH /api/tokos/{toko}  -> TokoController@update
// DELETE  /api/tokos/{toko}    -> TokoController@destroy
