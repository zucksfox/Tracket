<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\SparepartNotificationController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Public Guest Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Customer Tracking Portal (No Login Required)
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/', function () {
        return redirect()->route('tracking.index');
    });
    Route::get('/track', [TrackingController::class, 'index'])->name('tracking.index');
    Route::get('/track/search', [TrackingController::class, 'search'])->name('tracking.search');
    Route::get('/track/{service_code}', [TrackingController::class, 'show'])->name('tracking.show');
});

// Authenticated Backoffice Routes
Route::middleware(['auth', 'private-cache'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Service Orders
    Route::get('/services', [ServiceOrderController::class, 'index'])->name('services.index');
    Route::get('/services/{serviceOrder}', [ServiceOrderController::class, 'show'])->name('services.show');
    Route::post('/services/{serviceOrder}/update-status', [ServiceOrderController::class, 'updateStatus'])->middleware('role:admin,technician')->name('services.update-status');
    Route::post('/services/{serviceOrder}/add-part', [ServiceOrderController::class, 'addPart'])->middleware('role:admin,technician')->name('services.add-part');
    Route::delete('/services/{serviceOrder}/remove-part/{orderPart}', [ServiceOrderController::class, 'removePart'])->middleware('role:admin,technician')->name('services.remove-part');
    Route::post('/services/{serviceOrder}/checkout', [ServiceOrderController::class, 'checkout'])->name('services.checkout');
    Route::post('/services/{serviceOrder}/cancel', [ServiceOrderController::class, 'cancel'])->name('services.cancel');

    // Print Documents
    Route::get('/services/{serviceOrder}/print-receipt', [ServiceOrderController::class, 'printReceipt'])->name('services.print-receipt');
    Route::get('/services/{serviceOrder}/print-invoice', [ServiceOrderController::class, 'printInvoice'])->name('services.print-invoice');

    // Admin-Only Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/export', [ReportExportController::class, 'store'])->name('reports.export');
        Route::get('/reports/export/{file}', [ReportExportController::class, 'download'])
            ->where('file', '[A-Za-z0-9._-]+')
            ->name('reports.export.download');
    });

    Route::middleware('role:admin,cashier')->group(function () {
        // Service Check-In
        Route::get('/service/check-in', [ServiceOrderController::class, 'create'])->name('services.create');
        Route::post('/service/check-in', [ServiceOrderController::class, 'store'])->name('services.store');
        Route::get('/api/customer-lookup', [CustomerController::class, 'lookup'])->name('customers.lookup');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/api/stock-notifications', [SparepartNotificationController::class, 'index'])->name('stock-notifications.index');

        // Master Customers CRUD
        Route::resource('customers', CustomerController::class)->except(['show']);

        // Master Spareparts (Admin can full CRUD)
        Route::resource('spareparts', SparepartController::class)->except(['index', 'show']);

        // Master Teknisi & Akun Pengguna (Admin can full CRUD)
        Route::resource('technicians', TechnicianController::class)->except(['show']);
    });

    // Spareparts List (Accessible to both Admin and Technician)
    Route::get('/spareparts', [SparepartController::class, 'index'])->name('spareparts.index');
});
