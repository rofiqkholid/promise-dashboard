<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryOverviewController;
use App\Http\Controllers\Api\InventoryManagementController;
use App\Http\Controllers\Api\PartDetailController;
use App\Http\Controllers\Api\DrawingDetailController;


Route::get('/check-session-status', function () {
    return response()->json(['active' => Auth::check()]);
})->name('session.check');

Route::get('/login', function () {
    return redirect(env('PORTAL_LOGIN_URL'));
})->name('login');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    return redirect(env('PORTAL_LOGIN_URL'));
})->name('logout');

Route::middleware(['auth'])->group(function () {
    
    Route::get('/', function () {
        return view('all-dashboard');
    });

    Route::prefix('api')->group(function () {
        Route::get('/active-users-count', [\App\Http\Controllers\Api\DashboardController::class, 'getActiveUsersCount']);
        Route::get('/upload-count', [\App\Http\Controllers\Api\DashboardController::class, 'getUploadCount']);
        Route::get('/download-count', [\App\Http\Controllers\Api\DashboardController::class, 'getDownloadCount']);
        Route::get('/doc-count', [\App\Http\Controllers\Api\DashboardController::class, 'getDocCount']);
        
        Route::get('/log-data', [\App\Http\Controllers\Api\DashboardController::class, 'getDataLog']);
        Route::get('/get-save-env', [\App\Http\Controllers\Api\DashboardController::class, 'getSaveEnv']);
        Route::get('/upload-phase-status', [\App\Http\Controllers\Api\DashboardController::class, 'getPhaseStatus']);
        Route::get('/disk-space', [\App\Http\Controllers\Api\DashboardController::class, 'getDiskSpace']);
        
        Route::get('/log-data-activity', [\App\Http\Controllers\Api\DashboardController::class, 'getDataActivityLog']);
        Route::get('/upload-monitoring-data', [\App\Http\Controllers\Api\DashboardController::class, 'getUploadMonitoringData']);

        // Filter dropdown data
        Route::get('/customers', [\App\Http\Controllers\Api\DashboardController::class, 'getCustomers']);
        Route::get('/models', [\App\Http\Controllers\Api\DashboardController::class, 'getModels']);
        Route::get('/part-group', [\App\Http\Controllers\Api\DashboardController::class, 'getPartGroup']);
        Route::get('/status', [\App\Http\Controllers\Api\DashboardController::class, 'getStatus']);

        // Inventory Overview
        Route::get('/inventory-overview/data', [\App\Http\Controllers\Api\InventoryOverviewController::class, 'data']);
        Route::get('/inventory-overview/drilldown', [\App\Http\Controllers\Api\InventoryOverviewController::class, 'drilldown']);
        Route::match(['get', 'post'], '/inventory-overview/models', [\App\Http\Controllers\Api\InventoryOverviewController::class, 'getModels']);
        Route::match(['get', 'post'], '/inventory-overview/customers', [\App\Http\Controllers\Api\InventoryOverviewController::class, 'getCustomers']);
        Route::get('/inventory-overview/statuses/{type}', [\App\Http\Controllers\Api\InventoryOverviewController::class, 'getStatuses']);

    });
});
