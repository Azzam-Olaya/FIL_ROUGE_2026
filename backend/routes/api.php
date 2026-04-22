<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Freelancer\FreelancerController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/verify', [AuthController::class, 'verifyIdentity']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('role');
    });

    // Admin Routes
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'getStats']);
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/pending-users', [AdminController::class, 'getPendingUsers']);
        Route::post('/users/{id}/approve', [AdminController::class, 'approveUser']);
        Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser']);
        Route::post('/users/{id}/ban', [AdminController::class, 'banUser']);
        Route::post('/categories', [AdminController::class, 'createCategory']);
    });

    // Client Routes
    Route::prefix('client')->group(function () {
        Route::post('/missions', [ClientController::class, 'storeMission']);
        Route::get('/missions', [ClientController::class, 'getMyMissions']);
    });

    // Freelancer Routes
    Route::prefix('freelancer')->group(function () {
        Route::get('/missions', [FreelancerController::class, 'getAvailableMissions']);
        Route::post('/portfolios', [FreelancerController::class, 'storePortfolio']);
        Route::get('/portfolios', [FreelancerController::class, 'getMyPortfolios']);
    });

    // Chat & Messages
    Route::get('/contracts/{id}/messages', [\App\Http\Controllers\Api\MessageController::class, 'getConversation']);
    Route::post('/messages', [\App\Http\Controllers\Api\MessageController::class, 'sendMessage']);

    // Contracts
    Route::post('/contracts', [\App\Http\Controllers\Api\ContractController::class, 'store']);
    Route::post('/contracts/{id}/complete', [\App\Http\Controllers\Api\ContractController::class, 'complete']);
});
