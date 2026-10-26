<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TopsisController;
use App\Http\Middleware\RedirectOwnerFromStorefront;
use Illuminate\Support\Facades\Route;

// 1. PUBLIC STORE ROUTES
Route::middleware(RedirectOwnerFromStorefront::class)->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/produk', [HomeController::class, 'catalog'])->name('catalog');
    Route::get('/produk/{slug}', [HomeController::class, 'productDetail'])->name('product.detail');

    // CART & ONLINE CHECKOUT
    Route::get('/akun/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/akun/keranjang/tambah', [CartController::class, 'add'])->name('cart.add');
    Route::post('/akun/keranjang/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/akun/keranjang/hapus', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/akun/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/akun/checkout', [CartController::class, 'processCheckout'])->name('cart.checkout.process');

    Route::get('/akun/pesanan/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');
});

Route::post('/midtrans/notification', [OrderController::class, 'midtransNotification'])->name('midtrans.notification');

Route::get('/tentang', [HomeController::class, 'about'])->name('about');
Route::get('/kontak', [HomeController::class, 'contact'])->name('contact');

// 2. AUTHENTICATION (Multi-Role)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/masuk', fn () => redirect()->route('login'));

Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
Route::post('/daftar', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::get('/lupa-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/lupa-password', [PasswordResetController::class, 'sendOtp'])->name('password.email');
Route::get('/verifikasi-otp', [PasswordResetController::class, 'showOtpForm'])->name('password.otp.form');
Route::post('/verifikasi-otp', [PasswordResetController::class, 'verifyOtp'])->name('password.otp.verify');
Route::post('/kirim-ulang-otp', [PasswordResetController::class, 'resendOtp'])->name('password.otp.resend');
Route::get('/reset-password-baru', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password-baru', [PasswordResetController::class, 'resetPassword'])->name('password.reset.update');

// 3.1 USER PROFILE (Semua Role: Owner, Kasir, Pembeli)
Route::middleware(['auth'])->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::get('/admin/profil', fn () => redirect()->route('profile.edit'))->name('admin.profile');
    Route::get('/akun/riwayat-pesanan', [OrderController::class, 'index'])
        ->middleware('role:customer')
        ->name('orders.index');
});

// 4. CASHIER POS MODULE (Cashier & Owner)
Route::middleware(['auth', 'role:cashier,owner'])->prefix('kasir')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'index'])->name('index');
    Route::get('/pos', [CashierController::class, 'pos'])->name('pos');
    Route::post('/pos/transaksi', [CashierController::class, 'storeTransaction'])->name('store');
    Route::get('/transaksi', [CashierController::class, 'transactions'])->name('transactions');
    Route::get('/pesanan/{order:order_number}', [CashierController::class, 'showOrder'])->name('orders.show');
    Route::post('/pesanan/{order:order_number}/siap-diambil', [CashierController::class, 'markOrderReadyForPickup'])
        ->middleware('role:cashier')
        ->name('orders.ready');
    Route::post('/pesanan/{order:order_number}/selesai', [CashierController::class, 'completeOnlineOrder'])
        ->middleware('role:cashier')
        ->name('orders.complete');
});

// 5. OWNER / ADMIN & TOPSIS ENGINE (Owner)
Route::middleware(['auth', 'role:owner'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    // Products & Categories
    Route::get('/produk', [AdminController::class, 'products'])->name('products');
    Route::post('/produk', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::put('/produk/{id}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/produk/{id}', [AdminController::class, 'deleteProduct'])->name('products.delete');

    Route::get('/kategori', [AdminController::class, 'categories'])->name('categories');
    Route::post('/kategori', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::get('/stok', [AdminController::class, 'stockMovements'])->name('stock');
    Route::post('/stok', [AdminController::class, 'storeStockMovement'])->name('stock.store');

    // Orders & Proofs
    Route::get('/pesanan', [AdminController::class, 'orders'])->name('orders');
    Route::post('/pesanan/{id}/verifikasi', [AdminController::class, 'verifyOrder'])->name('orders.verify');

    // Customers
    Route::get('/pelanggan', [AdminController::class, 'customers'])->name('customers');
    Route::get('/pengguna', [AdminController::class, 'users'])->name('users');
    Route::post('/pengguna', [AdminController::class, 'storeCashier'])->name('users.store');
    Route::put('/pengguna/{id}', [AdminController::class, 'updateCashier'])->name('users.update');
    Route::get('/laporan', [AdminController::class, 'reports'])->name('reports');
    Route::get('/laporan/export', [AdminController::class, 'exportSalesReport'])->name('reports.export');

    // TOPSIS SPK MODULE
    Route::get('/topsis', [TopsisController::class, 'criteria'])->name('topsis.criteria');
    Route::post('/topsis/kriteria', [TopsisController::class, 'updateCriteria'])->name('topsis.criteria.update');
    Route::get('/topsis/perhitungan', [TopsisController::class, 'calculate'])->name('topsis.calculate');
    Route::get('/topsis/rekomendasi', [TopsisController::class, 'recommendations'])->name('topsis.recommendations');
});
