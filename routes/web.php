<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public Guest Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Customer Tracking Portal (No Login Required)
Route::get('/', function () {
    return redirect()->route('tracking.index');
});
Route::get('/track', [\App\Http\Controllers\TrackingController::class, 'index'])->name('tracking.index');
Route::get('/track/search', [\App\Http\Controllers\TrackingController::class, 'search'])->name('tracking.search');
Route::get('/track/{service_code}', [\App\Http\Controllers\TrackingController::class, 'show'])->name('tracking.show');

// Authenticated Backoffice Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Service Orders
    Route::get('/services', [\App\Http\Controllers\ServiceOrderController::class, 'index'])->name('services.index');
    Route::get('/services/{serviceOrder}', [\App\Http\Controllers\ServiceOrderController::class, 'show'])->name('services.show');
    Route::post('/services/{serviceOrder}/update-status', [\App\Http\Controllers\ServiceOrderController::class, 'updateStatus'])->name('services.update-status');
    Route::post('/services/{serviceOrder}/add-part', [\App\Http\Controllers\ServiceOrderController::class, 'addPart'])->name('services.add-part');
    Route::delete('/services/{serviceOrder}/remove-part/{orderPart}', [\App\Http\Controllers\ServiceOrderController::class, 'removePart'])->name('services.remove-part');
    Route::post('/services/{serviceOrder}/checkout', [\App\Http\Controllers\ServiceOrderController::class, 'checkout'])->name('services.checkout');
    Route::post('/services/{serviceOrder}/cancel', [\App\Http\Controllers\ServiceOrderController::class, 'cancel'])->name('services.cancel');
    
    // Print Documents
    Route::get('/services/{serviceOrder}/print-receipt', [\App\Http\Controllers\ServiceOrderController::class, 'printReceipt'])->name('services.print-receipt');
    Route::get('/services/{serviceOrder}/print-invoice', [\App\Http\Controllers\ServiceOrderController::class, 'printInvoice'])->name('services.print-invoice');

    // Admin-Only Routes
    Route::middleware('role:admin')->group(function () {
        // Service Check-In
        Route::get('/service/check-in', [\App\Http\Controllers\ServiceOrderController::class, 'create'])->name('services.create');
        Route::post('/service/check-in', [\App\Http\Controllers\ServiceOrderController::class, 'store'])->name('services.store');
        Route::get('/api/customer-lookup', [\App\Http\Controllers\CustomerController::class, 'lookup'])->name('customers.lookup');

        // Master Customers CRUD
        Route::resource('customers', \App\Http\Controllers\CustomerController::class)->except(['show']);

        // Master Spareparts (Admin can full CRUD)
        Route::resource('spareparts', \App\Http\Controllers\SparepartController::class)->except(['index', 'show']);

        // Master Teknisi & Akun Pengguna (Admin can full CRUD)
        Route::resource('technicians', \App\Http\Controllers\TechnicianController::class)->except(['show']);
    });

    // Spareparts List (Accessible to both Admin and Technician)
    Route::get('/spareparts', [\App\Http\Controllers\SparepartController::class, 'index'])->name('spareparts.index');
});
