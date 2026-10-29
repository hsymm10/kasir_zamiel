<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\StockLogController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ShiftController as AdminShiftController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;

use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Cashier\ChatController as CashierChatController;
use App\Http\Controllers\Cashier\ShiftController as CashierShiftController;
use App\Http\Controllers\Cashier\RefundController as CashierRefundController;

use App\Http\Controllers\InvoiceController;

// Public Auth Routes
Route::redirect('/', '/login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Public Invoice View (accessed via link / WA)
Route::get('/invoice/{invoice_number}', [InvoiceController::class, 'show'])->name('invoice.show');

// Super Admin Area
Route::middleware(['auth', 'admin', 'activity'])->prefix('admin')->as('admin.')->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Categories Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories/store', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/update/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/delete/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // Products Management
    Route::get('/products', [ProductController::class, 'index'])->name('products');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::post('/products/update/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/delete/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Supplier Management
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers');
    Route::post('/suppliers/store', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::post('/suppliers/update/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/delete/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // Stock Movement / Log
    Route::get('/stock-logs', [StockLogController::class, 'index'])->name('stock_logs');
    Route::post('/stock-logs/store', [StockLogController::class, 'store'])->name('stock_logs.store');
    
    // Cashier Accounts Management
    Route::get('/cashiers', [CashierController::class, 'index'])->name('cashiers');
    Route::post('/cashiers/store', [CashierController::class, 'store'])->name('cashiers.store');
    Route::post('/cashiers/update/{user}', [CashierController::class, 'update'])->name('cashiers.update');
    Route::delete('/cashiers/delete/{user}', [CashierController::class, 'destroy'])->name('cashiers.destroy');

    // Cashier Shift Report (Anti-Kecurangan)
    Route::get('/shifts', [AdminShiftController::class, 'index'])->name('shifts');
    
    // Transaction Reports
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('/transactions/export', [TransactionController::class, 'exportCsv'])->name('transactions.export');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    // Expenses Management
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses');
    Route::post('/expenses/store', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/delete/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Refunds / Retur Report
    Route::get('/refunds', [AdminRefundController::class, 'index'])->name('refunds');
    
    // Admin Chat Center
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::get('/chat/unread-count', [ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::get('/chat/messages/{cashierId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/online-status', [ChatController::class, 'getOnlineStatus'])->name('chat.online-status');

    // Application Settings (White-Label Customization)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');
});

// Cashier Area
Route::middleware(['auth', 'cashier', 'activity'])->prefix('cashier')->as('cashier.')->group(function() {
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::get('/pos/products', [PosController::class, 'getProducts'])->name('pos.products');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

    // Cashier Shift Endpoints
    Route::get('/shift/current', [CashierShiftController::class, 'currentShift'])->name('shift.current');
    Route::post('/shift/open', [CashierShiftController::class, 'openShift'])->name('shift.open');
    Route::post('/shift/close', [CashierShiftController::class, 'closeShift'])->name('shift.close');

    // Cashier Refund / Retur Barang
    Route::get('/refunds', [CashierRefundController::class, 'index'])->name('refunds.index');
    Route::post('/refunds/process', [CashierRefundController::class, 'processRefund'])->name('refunds.process');
    
    // Cashier Floating Chat
    Route::get('/chat/unread-count', [CashierChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::get('/chat/messages', [CashierChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [CashierChatController::class, 'sendMessage'])->name('chat.send');
});
