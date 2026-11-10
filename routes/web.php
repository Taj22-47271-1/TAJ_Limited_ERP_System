<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\LoginOfferController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\OnlinePaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Render health check
Route::get('/healthz', function () {
    try {
        DB::connection()->getPdo();
        $hasUsers = Schema::hasTable('users');
        $userCount = $hasUsers ? DB::table('users')->count() : 0;
        return response()->json([
            'status' => 'ok',
            'database' => 'connected',
            'users_table_exists' => $hasUsers,
            'users_count' => $userCount,
        ]);
    } catch (\Throwable $e) {
        $cleanMsg = preg_replace('/password=[^; ]+/i', 'password=***', $e->getMessage());
        return response()->json([
            'status' => 'error',
            'database' => 'disconnected',
            'error' => $cleanMsg,
        ], 500);
    }
});

// Root redirect: Unauthenticated users go directly to login; authenticated users go to dashboard
Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

// Public Storefront Browsing (Guests, Customers & Staff)
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/products/{product}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');

// Guest authentication and password reset routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.email');
    Route::get('/forgot-password/verify-otp', [ForgotPasswordController::class, 'showVerify'])->name('password.otp.form');
    Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showReset'])->name('password.reset.form');
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/profile/change-password', [ChangePasswordController::class, 'edit'])->name('password.change');
    Route::put('/profile/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');

    // Customer profile & order management (accessible to all authenticated customer, admin & staff shoppers)
    Route::middleware('role:customer,admin,cashier')->group(function () {
        Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('customer.profile');
        Route::put('/profile', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
        Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [OnlineOrderController::class, 'store'])->name('checkout.store');
        Route::get('/my-orders', [OnlineOrderController::class, 'customerIndex'])->name('customer.orders');
        Route::get('/my-orders/{order}', [OnlineOrderController::class, 'customerShow'])->name('customer.orders.show');
        Route::post('/my-orders/{order}/cancel', [OnlineOrderController::class, 'cancel'])->name('customer.orders.cancel');
        Route::get('/my-orders/{order}/payment', [OnlinePaymentController::class, 'show'])->name('customer.orders.pay');
        Route::post('/my-orders/{order}/payment', [OnlinePaymentController::class, 'pay'])->name('customer.orders.pay.store');
    });

    // Product catalog. Admin and Cashier can add/edit products; only Admin controls stock/delete.
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    });
    Route::middleware('permission:products.manage')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::resource('categories', CategoryController::class)->only(['index','store','update','destroy']);
    });
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('permission:inventory.manage')->name('products.destroy');

    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('/inventory/stock-movements', [StockMovementController::class, 'index'])->name('inventory.movements');
    });
    Route::post('/inventory/adjust', [InventoryAdjustmentController::class, 'store'])->middleware('permission:inventory.manage')->name('inventory.adjust');

    Route::resource('customers', CustomerController::class)->only(['index','store','update'])->middleware('permission:customers.manage');
    Route::resource('suppliers', SupplierController::class)->only(['index','store','update'])->middleware('permission:suppliers.manage');
    Route::resource('purchases', PurchaseController::class)->only(['index','create','store','show'])->middleware('permission:purchases.manage');

    // In-house POS sale.
    Route::get('/pos', [SaleController::class, 'create'])->middleware('permission:pos.use')->name('pos');
    Route::post('/sales', [SaleController::class, 'store'])->middleware('permission:pos.use')->name('sales.store');
    Route::get('/sales', [SaleController::class, 'index'])->middleware('permission:sales.view')->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->middleware('permission:sales.view')->name('sales.show');

    // Online order operations for Admin / Cashier.
    Route::middleware('permission:orders.view')->group(function () {
        Route::get('/orders', [OnlineOrderController::class, 'staffIndex'])->name('orders.index');
        Route::get('/orders/{order}', [OnlineOrderController::class, 'staffShow'])->name('orders.show');
    });
    Route::post('/orders/{order}/approve', [OnlineOrderController::class, 'approve'])->middleware('permission:orders.approve')->name('orders.approve');
    Route::post('/orders/{order}/progress', [OnlineOrderController::class, 'progress'])->middleware('permission:orders.progress')->name('orders.progress');
    Route::post('/orders/{order}/reject', [OnlineOrderController::class, 'reject'])->middleware('role:admin')->name('orders.reject');

    // Admin-only finance, reports and system administration.
    Route::resource('expenses', ExpenseController::class)->only(['index','store'])->middleware('permission:accounting.manage');
    Route::get('/accounting/journals', [JournalController::class, 'index'])->middleware('permission:accounting.view')->name('journals.index');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->middleware('permission:reports.view')->name('reports.profit-loss');
    Route::get('/reports/sales.csv', [ReportController::class, 'salesCsv'])->middleware('permission:reports.view')->name('reports.sales.csv');

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    });
    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('/admin/roles', [RoleController::class, 'index'])->name('admin.roles.index');
        Route::post('/admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
        Route::put('/admin/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
        Route::delete('/admin/roles/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
    });
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/admin/settings', [SettingsController::class, 'edit'])->name('admin.settings');
        Route::put('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    });
    Route::middleware('permission:showcase.manage')->group(function () {
        Route::get('/admin/showcase', [ShowcaseController::class, 'edit'])->name('admin.showcase');
        Route::put('/admin/showcase', [ShowcaseController::class, 'update'])->name('admin.showcase.update');
        Route::get('/admin/login-offers', [LoginOfferController::class, 'edit'])->name('admin.login-offers');
        Route::put('/admin/login-offers', [LoginOfferController::class, 'update'])->name('admin.login-offers.update');
    });
});
