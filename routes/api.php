<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes - Simplified Version
|--------------------------------------------------------------------------
*/

// Get authenticated user (existing)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Authentication Routes (No Middleware Required)
|--------------------------------------------------------------------------
*/

// Universal login for both admin and collector
Route::post('login', 'App\Http\Controllers\AuthController@login');

// Protected auth routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
    Route::get('profile', 'App\Http\Controllers\AuthController@profile');
    Route::post('refresh-token', 'App\Http\Controllers\AuthController@refreshToken');
    Route::apiResource('collection', 'App\Http\Controllers\CollectionController');
});

Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/collector/{collectorId}', [DashboardController::class, 'collectorDashboard']);
    Route::get('/summary', [DashboardController::class, 'collectionsSummary']);
});

/*
|--------------------------------------------------------------------------
| Machine Routes
|--------------------------------------------------------------------------
*/

// Machine CRUD (existing)
Route::apiResource('machines', 'App\Http\Controllers\MachineController');

// Machine validation (existing)
Route::post('validate-machine', 'App\Http\Controllers\MachineController@validateMachine');

/*
|--------------------------------------------------------------------------
| Collection Routes
|--------------------------------------------------------------------------
*/

// Collection CRUD (existing)

/*
|--------------------------------------------------------------------------
| Health & Status Routes
|--------------------------------------------------------------------------
*/

// Health check (existing - improved)
Route::get('health', function(){
    Log::info("API Health Check - PINGED");
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'Collection API'
    ]);
});

// API status endpoint
Route::get('status', function () {
    return response()->json([
        'api_version' => '1.0.0',
        'status' => 'operational',
        'timestamp' => now()->toISOString(),
        'endpoints' => [
            'login' => '/api/login',
            'logout' => '/api/logout',
            'profile' => '/api/profile',
            'machines' => '/api/machines',
            'collections' => '/api/collection',
            'health' => '/api/health',
        ],
    ]);
});

/*
|--------------------------------------------------------------------------
| Future Transaction Routes (Placeholder)
|--------------------------------------------------------------------------
*/

// Transaction routes - uncomment when needed
// Route::apiResource('transactions', 'App\Http\Controllers\TransactionController');