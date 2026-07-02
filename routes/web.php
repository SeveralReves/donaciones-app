<?php

use App\Http\Controllers\Admin\AdditionalNeedController;
use App\Http\Controllers\Admin\StockItemController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\NeedsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Pública a propósito: se comparte por WhatsApp, sin login ni CSRF de por
// medio. Es de solo lectura, no expone ninguna acción de escritura.
Route::get('/necesidades', [NeedsController::class, 'index'])->name('needs.index');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('donations', DonationController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('donations/{donation}/status', [DonationController::class, 'updateStatus'])
        ->name('donations.update-status');
});

Route::middleware(['auth', 'can:manage-users'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class)->except(['show', 'destroy']);
    Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])
        ->name('users.reset-password');
});

Route::middleware(['auth', 'can:manage-stock'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('stock-items', StockItemController::class)->only(['index', 'create', 'store']);
    Route::get('stock-items/{stockItem}/adjustments', [StockItemController::class, 'adjustments'])
        ->name('stock-items.adjustments');
    Route::patch('stock-items/{stockItem}/adjust', [StockItemController::class, 'adjustStock'])
        ->name('stock-items.adjust');
    Route::patch('stock-items/{stockItem}/threshold', [StockItemController::class, 'updateThreshold'])
        ->name('stock-items.update-threshold');

    Route::resource('needs', AdditionalNeedController::class)->only(['index', 'store']);
    Route::patch('needs/{additionalNeed}/toggle-active', [AdditionalNeedController::class, 'toggleActive'])
        ->name('needs.toggle-active');
});

require __DIR__.'/auth.php';
