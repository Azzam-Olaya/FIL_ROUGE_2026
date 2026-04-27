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
        // Signalements
        Route::get('/reports', [\App\Http\Controllers\Api\ReportController::class, 'index']);
        Route::post('/reports/{id}/dismiss', [\App\Http\Controllers\Api\ReportController::class, 'dismiss']);
        Route::post('/reports/{id}/ban', [\App\Http\Controllers\Api\ReportController::class, 'ban']);
    });

    // Signalement (client & freelancer)
    Route::post('/reports', [\App\Http\Controllers\Api\ReportController::class, 'store']);

    // Client
    Route::prefix('client')->group(function () {
        Route::get('/categories', [FreelancerController::class, 'getCategories']);
        Route::post('/missions', [ClientController::class, 'storeMission']);
        Route::get('/missions', [ClientController::class, 'getMyMissions']);
        Route::get('/stats', [ClientController::class, 'getStats']);
        Route::get('/briefs', [ClientController::class, 'getBriefs']);
        Route::post('/briefs/{id}/like', [ClientController::class, 'toggleLike']);
        Route::post('/briefs/{id}/comment', [ClientController::class, 'addComment']);
        Route::get('/briefs/{id}/comments', [ClientController::class, 'getComments']);
        Route::post('/briefs/{id}/favorite', [ClientController::class, 'toggleFavorite']);
        Route::get('/favorites', [ClientController::class, 'getMyFavorites']);
        Route::get('/notifications', [ClientController::class, 'getNotifications']);
        Route::get('/missions/{id}/applications', [ClientController::class, 'getApplications']);
        Route::post('/applications/{id}/accept', [ClientController::class, 'acceptApplication']);
        Route::post('/test-credit', [ClientController::class, 'testCredit']);
    });

    // Freelancer
    Route::prefix('freelancer')->group(function () {
        Route::get('/briefs', [FreelancerController::class, 'getBriefs']);
        Route::get('/briefs/mine', [FreelancerController::class, 'getMyBriefs']);
        Route::post('/briefs', [FreelancerController::class, 'storeBrief']);
        Route::post('/briefs/{id}/like', [FreelancerController::class, 'toggleLike']);
        Route::post('/briefs/{id}/comment', [FreelancerController::class, 'addComment']);
        Route::get('/briefs/{id}/comments', [FreelancerController::class, 'getComments']);
        Route::post('/briefs/{id}/favorite', [FreelancerController::class, 'toggleFavorite']);
        Route::get('/missions', [FreelancerController::class, 'getAvailableMissions']);
        Route::get('/missions/favorites', [FreelancerController::class, 'getMyMissionFavorites']);
        Route::post('/missions/{id}/like', [FreelancerController::class, 'toggleMissionLike']);
        Route::post('/missions/{id}/favorite', [FreelancerController::class, 'toggleMissionFavorite']);
        Route::post('/missions/{id}/comment', [FreelancerController::class, 'addMissionComment']);
        Route::get('/missions/{id}/comments', [FreelancerController::class, 'getMissionComments']);
        Route::get('/favorites', [FreelancerController::class, 'getMyFavorites']);
        Route::get('/notifications', [FreelancerController::class, 'getNotifications']);
        Route::get('/categories', [FreelancerController::class, 'getCategories']);
        Route::post('/missions/{id}/apply', [FreelancerController::class, 'applyToMission']);
    });

    // Messages
    Route::get('/conversations', [\App\Http\Controllers\Api\MessageController::class, 'getConversations']);
    Route::get('/conversations/{userId}', [\App\Http\Controllers\Api\MessageController::class, 'getMessages']);
    Route::post('/messages', [\App\Http\Controllers\Api\MessageController::class, 'sendMessage']);

    // Contracts
    Route::post('/contracts', [\App\Http\Controllers\Api\ContractController::class, 'store']);
    Route::post('/contracts/{id}/complete', [\App\Http\Controllers\Api\ContractController::class, 'complete']);
    Route::post('/contracts/{id}/refund', [\App\Http\Controllers\Api\ContractController::class, 'refund']);
    Route::get('/contracts/client', [\App\Http\Controllers\Api\ContractController::class, 'clientContracts']);
    Route::get('/contracts/freelancer', [\App\Http\Controllers\Api\ContractController::class, 'freelancerContracts']);
    Route::post('/contracts/{id}/accept', [\App\Http\Controllers\Api\ContractController::class, 'acceptByFreelancer']);
    Route::post('/contracts/{id}/reject', [\App\Http\Controllers\Api\ContractController::class, 'rejectByFreelancer']);

    // Wallet
    Route::get('/wallet/summary', [\App\Http\Controllers\Api\WalletController::class, 'getSummary']);
    Route::post('/wallet/paypal/capture', [\App\Http\Controllers\Api\WalletController::class, 'capturePayPalOrder']);
});
