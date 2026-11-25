<?php

use App\Http\Controllers\Admin\Catalog\CategoryController;
use App\Http\Controllers\Admin\Catalog\ProductController;
use App\Http\Controllers\Admin\Catalog\UnitController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Program\NotificationController;
use App\Http\Controllers\Admin\Program\BannerController;
use App\Http\Controllers\Admin\Program\FeedbackController;
use App\Http\Controllers\Admin\Program\RewardController;
use App\Http\Controllers\Admin\Program\RewardRedemptionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RekeningController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\Transaction\CustomerController;
use App\Http\Controllers\Admin\Transaction\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'landing'])->name('landing');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::redirect('/master/product', '/katalog/produk', 301);
Route::get('/master/product/{path}', fn (string $path) => redirect('/katalog/produk/'.$path, 301))->where('path', '.*');
Route::redirect('/master/category', '/katalog/kategori', 301);
Route::get('/master/category/{path}', fn (string $path) => redirect('/katalog/kategori/'.$path, 301))->where('path', '.*');
Route::redirect('/master/banner', '/katalog/banner', 301);
Route::get('/master/banner/{path}', fn (string $path) => redirect('/katalog/banner/'.$path, 301))->where('path', '.*');
Route::redirect('/master/orders', '/transaksi/pesanan', 301);
Route::get('/master/orders/{path}', fn (string $path) => redirect('/transaksi/pesanan/'.$path, 301))->where('path', '.*');
Route::redirect('/master/customers', '/transaksi/pelanggan', 301);
Route::get('/master/customers/{path}', fn (string $path) => redirect('/transaksi/pelanggan/'.$path, 301))->where('path', '.*');
Route::redirect('/master/analytics', '/utama/analitik', 301);
Route::redirect('/master/program/rewards', '/program/program-reward', 301);
Route::get('/master/program/rewards/{path}', fn (string $path) => redirect('/program/program-reward/'.$path, 301))->where('path', '.*');
Route::redirect('/master/program/claims', '/program/klaim-hadiah', 301);
Route::get('/master/program/claims/{path}', fn (string $path) => redirect('/program/klaim-hadiah/'.$path, 301))->where('path', '.*');
Route::redirect('/master/feedback', '/program/kritik-saran', 301);
Route::get('/master/feedback/{path}', fn (string $path) => redirect('/program/kritik-saran/'.$path, 301))->where('path', '.*');
Route::redirect('/master/reports/sales', '/laporan/penjualan', 301);
Route::redirect('/master/reports/stock', '/laporan/stok', 301);
Route::redirect('/master/reports/customers', '/laporan/pelanggan', 301);
Route::redirect('/master/reports/points', '/laporan/poin', 301);
Route::redirect('/master/settings', '/sistem/pengaturan', 301);
Route::redirect('/master/user', '/sistem/pengguna', 301);
Route::get('/master/user/{path}', fn (string $path) => redirect('/sistem/pengguna/'.$path, 301))->where('path', '.*');
Route::get('/master/{path}', fn (string $path) => redirect('/'.$path, 301))->where('path', '.*');

Route::middleware('admin.session')->group(function () {
    // Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'analytics'])->name('dashboard');
    // Route::get('/utama/analitik', [DashboardController::class, 'analytics'])->name('analytics');

    Route::get('/katalog/produk/cari', [ProductController::class, 'search'])->name('products.search');
    Route::get('/katalog/produk', [ProductController::class, 'index'])->name('products.index');
    Route::get('/katalog/produk/new', [ProductController::class, 'create'])->name('products.create');
    Route::post('/katalog/produk', [ProductController::class, 'store'])->name('products.store');
    Route::get('/katalog/produk/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/katalog/produk/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::get('/katalog/produk/{id}/status', fn () => redirect()->route('products.index'));
    Route::patch('/katalog/produk/{id}/status', [ProductController::class, 'status'])->name('products.status');
    Route::patch('/katalog/produk/{id}/availability', [ProductController::class, 'availability'])->name('products.availability');
    Route::delete('/katalog/produk/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/katalog/kategori', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/katalog/kategori/new', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/katalog/kategori', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/katalog/kategori/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/katalog/kategori/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/katalog/kategori/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/katalog/satuan', [UnitController::class, 'index'])->name('units.index');
    Route::get('/katalog/satuan/new', [UnitController::class, 'create'])->name('units.create');
    Route::post('/katalog/satuan', [UnitController::class, 'store'])->name('units.store');
    Route::get('/katalog/satuan/{id}/edit', [UnitController::class, 'edit'])->name('units.edit');
    Route::put('/katalog/satuan/{id}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('/katalog/satuan/{id}', [UnitController::class, 'destroy'])->name('units.destroy');

    Route::get('/transaksi/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/transaksi/pesanan/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/transaksi/pesanan/{id}/status', [OrderController::class, 'status'])->name('orders.status');
    Route::get('/transaksi/pelanggan', [CustomerController::class, 'index'])->name('customers.index');

    Route::get('/program/program-reward', [RewardController::class, 'index'])->name('rewards.index');
    Route::get('/program/program-reward/new', [RewardController::class, 'create'])->name('rewards.create');
    Route::post('/program/program-reward', [RewardController::class, 'store'])->name('rewards.store');
    Route::get('/program/program-reward/{id}/edit', [RewardController::class, 'edit'])->name('rewards.edit');
    Route::put('/program/program-reward/{id}', [RewardController::class, 'update'])->name('rewards.update');
    Route::patch('/program/program-reward/{id}/toggle', [RewardController::class, 'toggle'])->name('rewards.toggle');
    Route::delete('/program/program-reward/{id}', [RewardController::class, 'destroy'])->name('rewards.destroy');
 
    Route::get('/program/klaim-hadiah', [RewardRedemptionController::class, 'index'])->name('redemptions.index');
    Route::patch('/program/klaim-hadiah/{id}/status', [RewardRedemptionController::class, 'status'])->name('redemptions.status');
    Route::get('/program/kritik-saran', [FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('/program/kritik-saran/{id}', [FeedbackController::class, 'show'])->name('feedbacks.show');
    Route::post('/program/kritik-saran/{id}/reply', [FeedbackController::class, 'reply'])->name('feedbacks.reply');
    Route::delete('/program/kritik-saran/{id}', [FeedbackController::class, 'destroy'])->name('feedbacks.destroy');

    Route::get('/program/banner', [BannerController::class, 'index'])->name('banners.index');
    Route::get('/program/banner/new', [BannerController::class, 'create'])->name('banners.create');
    Route::post('/program/banner', [BannerController::class, 'store'])->name('banners.store');
    Route::get('/program/banner/{id}/edit', [BannerController::class, 'edit'])->name('banners.edit');
    Route::put('/program/banner/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/program/banner/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');

    Route::get('/program/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/program/notifikasi/new', [NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/program/notifikasi', [NotificationController::class, 'store'])->name('notifications.store');
    Route::get('/program/notifikasi/{id}/edit', [NotificationController::class, 'edit'])->name('notifications.edit');
    Route::put('/program/notifikasi/{id}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::delete('/program/notifikasi/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/laporan/{type}', [ReportController::class, 'show'])
        ->whereIn('type', ['penjualan', 'pelanggan', 'poin', 'stok'])
        ->name('reports.show');

    Route::get('/sistem/pengaturan', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/sistem/pengaturan', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/sistem/pengaturan/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('/sistem/pengaturan/admins', [SettingsController::class, 'storeAdmin'])->name('settings.admins.store');
    Route::patch('/sistem/pengaturan/admins/{id}/toggle', [SettingsController::class, 'toggleAdmin'])->name('settings.admins.toggle');
    Route::delete('/sistem/pengaturan/admins/{id}', [SettingsController::class, 'destroyAdmin'])->name('settings.admins.destroy');

    Route::get('/sistem/rekening', [RekeningController::class, 'index'])->name('rekenings.index');
    Route::get('/sistem/rekening/new', [RekeningController::class, 'create'])->name('rekenings.create');
    Route::post('/sistem/rekening', [RekeningController::class, 'store'])->name('rekenings.store');
    Route::get('/sistem/rekening/{id}/edit', [RekeningController::class, 'edit'])->name('rekenings.edit');
    Route::put('/sistem/rekening/{id}', [RekeningController::class, 'update'])->name('rekenings.update');
    Route::patch('/sistem/rekening/{id}/toggle', [RekeningController::class, 'toggle'])->name('rekenings.toggle');
    Route::delete('/sistem/rekening/{id}', [RekeningController::class, 'destroy'])->name('rekenings.destroy');

    Route::get('/sistem/pengguna/cari', [UserController::class, 'getCustomers'])->name('users.search');
    Route::get('/sistem/pengguna', [UserController::class, 'index'])->name('users.index');
    Route::get('/sistem/pengguna/new', [UserController::class, 'create'])->name('users.create');
    Route::post('/sistem/pengguna', [UserController::class, 'store'])->name('users.store');
    Route::get('/sistem/pengguna/{id}/detail', [UserController::class, 'detail'])->name('users.detail');
    Route::get('/sistem/pengguna/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/sistem/pengguna/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/sistem/pengguna/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/sistem/profil', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/sistem/profil', [ProfileController::class, 'update'])->name('profile.update');
});
