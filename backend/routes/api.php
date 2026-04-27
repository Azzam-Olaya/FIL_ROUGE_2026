<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Freelancer\FreelancerController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/debug-logs', function () {
    $path = storage_path('logs/laravel.log');
    if (!file_exists($path)) return 'No log file';
    
    // Read the last 200 lines
    $lines = file($path);
    if (!$lines) return 'Empty log';
    
    return implode("", array_slice($lines, -200));
});

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
        Route::get('/missions/{id}/comments', [ClientController::class, 'getMissionComments']);
        Route::get('/missions/{id}/likes', [ClientController::class, 'getMissionLikes']);
        Route::get('/stats', [ClientController::class, 'getStats']);
        Route::get('/payments', [ClientController::class, 'getPayments']);
        Route::get('/briefs', [ClientController::class, 'getBriefs']);
        Route::post('/briefs/{id}/like', [ClientController::class, 'toggleLike']);
        Route::post('/briefs/{id}/comment', [ClientController::class, 'addComment']);
        Route::get('/briefs/{id}/comments', [ClientController::class, 'getComments']);
        Route::post('/briefs/{id}/favorite', [ClientController::class, 'toggleFavorite']);
        Route::get('/favorites', [ClientController::class, 'getMyFavorites']);
        Route::get('/notifications', [ClientController::class, 'getNotifications']);
        Route::post('/notifications/read', [ClientController::class, 'markAllRead']);
        Route::patch('/notifications/{id}/read', [ClientController::class, 'markOneRead']);
        Route::get('/missions/{id}/applications', [ClientController::class, 'getApplications']);
        Route::post('/applications/{id}/accept', [ClientController::class, 'acceptApplication']);
        Route::post('/test-credit', [ClientController::class, 'testCredit']);
    });

    // Freelancer
    Route::prefix('freelancer')->group(function () {
        Route::get('/stats', [FreelancerController::class, 'getStats']);
        Route::get('/balance', [FreelancerController::class, 'getBalance']);
        Route::get('/briefs', [FreelancerController::class, 'getBriefs']);
        Route::get('/briefs/mine', [FreelancerController::class, 'getMyBriefs']);
        Route::post('/briefs', [FreelancerController::class, 'storeBrief']);
        Route::post('/briefs/{id}/like', [FreelancerController::class, 'toggleLike']);
        Route::post('/briefs/{id}/comment', [FreelancerController::class, 'addComment']);
        Route::get('/briefs/{id}/comments', [FreelancerController::class, 'getComments']);
        Route::post('/briefs/{id}/favorite', [FreelancerController::class, 'toggleFavorite']);
        Route::get('/missions', [FreelancerController::class, 'getAvailableMissions']);
        Route::get('/missions/active', [FreelancerController::class, 'getActiveMissions']);
        Route::get('/missions/favorites', [FreelancerController::class, 'getMyMissionFavorites']);
        Route::post('/missions/{id}/like', [FreelancerController::class, 'toggleMissionLike']);
        Route::post('/missions/{id}/favorite', [FreelancerController::class, 'toggleMissionFavorite']);
        Route::post('/missions/{id}/comment', [FreelancerController::class, 'addMissionComment']);
        Route::get('/missions/{id}/comments', [FreelancerController::class, 'getMissionComments']);
        Route::get('/favorites', [FreelancerController::class, 'getMyFavorites']);
        Route::get('/payments', [FreelancerController::class, 'getPayments']);
        Route::get('/profile', [FreelancerController::class, 'getProfile']);
        Route::put('/profile', [FreelancerController::class, 'updateProfile']);
        Route::put('/profile/password', [FreelancerController::class, 'changePassword']);
        Route::get('/notifications', [FreelancerController::class, 'getNotifications']);
        Route::get('/suggested', [FreelancerController::class, 'getSuggestedFreelancers']);
        Route::get('/categories', [FreelancerController::class, 'getCategories']);
        Route::post('/missions/{id}/apply', [FreelancerController::class, 'applyToMission']);
    });

    // Messages
    Route::get('/conversations', [\App\Http\Controllers\Api\MessageController::class, 'getConversations']);
    Route::get('/conversations/{userId}', [\App\Http\Controllers\Api\MessageController::class, 'getMessages']);
    Route::post('/messages', [\App\Http\Controllers\Api\MessageController::class, 'sendMessage']);
    Route::get('/contracts/{id}/messages', [\App\Http\Controllers\Api\MessageController::class, 'getConversation']);

    // Contracts
    Route::post('/contracts', [\App\Http\Controllers\Api\ContractController::class, 'store']);
    Route::put('/contracts/{id}', [\App\Http\Controllers\Api\ContractController::class, 'update']);
    Route::post('/contracts/{id}/complete', [\App\Http\Controllers\Api\ContractController::class, 'complete']);
    Route::post('/contracts/{id}/refund', [\App\Http\Controllers\Api\ContractController::class, 'refund']);
    Route::get('/contracts/client', [\App\Http\Controllers\Api\ContractController::class, 'clientContracts']);
    Route::get('/contracts/freelancer', [\App\Http\Controllers\Api\ContractController::class, 'freelancerContracts']);
    Route::post('/contracts/{id}/accept', [\App\Http\Controllers\Api\ContractController::class, 'acceptByFreelancer']);
    Route::post('/contracts/{id}/reject', [\App\Http\Controllers\Api\ContractController::class, 'rejectByFreelancer']);

    // Wallet
    Route::get('/wallet/summary', [\App\Http\Controllers\Api\WalletController::class, 'getSummary']);
    Route::get('/wallet/history', [\App\Http\Controllers\Api\WalletController::class, 'getHistory']);
    Route::post('/wallet/deposit', [\App\Http\Controllers\Api\WalletController::class, 'addFunds']);
    Route::post('/wallet/paypal/capture', [\App\Http\Controllers\Api\WalletController::class, 'capturePayPalOrder']);
});
