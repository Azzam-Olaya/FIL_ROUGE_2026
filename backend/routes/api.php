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
    Route::get('/user', fn(Request $r) => $r->user()->load('role'));

    // Admin
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'getStats']);
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/pending-users', [AdminController::class, 'getPendingUsers']);
        Route::post('/users/{id}/approve', [AdminController::class, 'approveUser']);
        Route::post('/users/{id}/reject', [AdminController::class, 'rejectUser']);
        Route::post('/users/{id}/ban', [AdminController::class, 'banUser']);
        Route::get("/categories", [AdminController::class, "getCategories"]);
        Route::post("/categories", [AdminController::class, "createCategory"]);
        Route::put("/categories/{id}", [AdminController::class, "updateCategory"]);
        Route::delete("/categories/{id}", [AdminController::class, "deleteCategory"]);
    });

    // Client
    Route::prefix('client')->group(function () {
        Route::post('/missions', [ClientController::class, 'storeMission']);
        Route::get('/missions', [ClientController::class, 'getMyMissions']);
        Route::get('/stats', [ClientController::class, 'getStats']);
    });

    // Freelancer
    Route::prefix('freelancer')->group(function () {
        Route::get('/stats', [FreelancerController::class, 'getStats']);
        Route::get('/balance', [FreelancerController::class, 'getBalance']);
        Route::get('/missions/active', [FreelancerController::class, 'getActiveMissions']);
        Route::get('/categories', [FreelancerController::class, 'getCategories']);
        Route::get('/missions', [FreelancerController::class, 'getAvailableMissions']);
        Route::get('/briefs', [FreelancerController::class, 'getBriefs']);
        Route::get('/briefs/mine', [FreelancerController::class, 'getMyBriefs']);
        Route::post('/briefs', [FreelancerController::class, 'storeBrief']);
        Route::post('/briefs/{id}/like', [FreelancerController::class, 'toggleLike']);
        Route::post('/briefs/{id}/comment', [FreelancerController::class, 'addComment']);
        Route::get('/briefs/{id}/comments', [FreelancerController::class, 'getComments']);
        Route::post('/briefs/{id}/favorite', [FreelancerController::class, 'toggleFavorite']);
        Route::get('/favorites', [FreelancerController::class, 'getMyFavorites']);
        Route::get('/payments', [FreelancerController::class, 'getPayments']);
        Route::get('/profile', [FreelancerController::class, 'getProfile']);
        Route::put('/profile', [FreelancerController::class, 'updateProfile']);
        Route::put('/profile/password', [FreelancerController::class, 'changePassword']);
        Route::get('/notifications', [FreelancerController::class, 'getNotifications']);
        Route::get('/suggested', [FreelancerController::class, 'getSuggestedFreelancers']);
    });

    // Messages
    Route::get('/contracts/{id}/messages', [\App\Http\Controllers\Api\MessageController::class, 'getConversation']);
    Route::post('/messages', [\App\Http\Controllers\Api\MessageController::class, 'sendMessage']);

    // Contracts
    Route::post('/contracts', [\App\Http\Controllers\Api\ContractController::class, 'store']);
    Route::post('/contracts/{id}/complete', [\App\Http\Controllers\Api\ContractController::class, 'complete']);
});
