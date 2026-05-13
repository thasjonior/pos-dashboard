<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('machines', Admin\MachineController::class);
    Route::resource('device-accounts', Admin\DeviceAccountController::class)
        ->only(['index', 'show', 'edit', 'update'])
        ->parameters(['device-accounts' => 'collector']);
    Route::post('device-accounts/{collector}/deactivate', [Admin\DeviceAccountController::class, 'deactivate'])->name('device-accounts.deactivate');
    // Redirect legacy collector URLs
    Route::get('collectors', fn () => redirect()->route('admin.device-accounts.index'));
    Route::get('collectors/{id}', fn ($id) => redirect()->route('admin.device-accounts.show', $id));
    Route::get('collectors/{id}/edit', fn ($id) => redirect()->route('admin.device-accounts.edit', $id));
    Route::get('collections/export', [Admin\CollectionController::class, 'export'])->name('collections.export');
    Route::resource('collections', Admin\CollectionController::class)->only(['index', 'show']);
    Route::resource('companies', Admin\CompanyController::class);
    Route::resource('devices', Admin\DeviceController::class)->only(['index', 'show']);

    Route::get('search', [Admin\SearchController::class, 'index'])->name('search');
    Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [Admin\ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::middleware('super_admin')->group(function () {
        Route::post('devices/{device}/wipe', [Admin\DeviceController::class, 'wipe'])->name('devices.wipe');
        Route::post('devices/{device}/mark-executed', [Admin\DeviceController::class, 'markExecuted'])->name('devices.mark-executed');
        Route::resource('audit-logs', Admin\AuditLogController::class)->only(['index', 'show']);
        Route::resource('users', Admin\UserManagementController::class);
        Route::post('users/{user}/deactivate', [Admin\UserManagementController::class, 'deactivate'])->name('users.deactivate');
    });
});
