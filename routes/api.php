<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profile\SitterProfileController;
use App\Http\Controllers\Pets\PetController;

// Marketplace Controllers
use App\Http\Controllers\Marketplace\DemandController;
use App\Http\Controllers\Marketplace\BidController;
use App\Http\Controllers\Marketplace\BookingController;

// Payments Controllers
use App\Http\Controllers\Payments\WalletController;
use App\Http\Controllers\Payments\TransactionController;
use App\Http\Controllers\Payments\MangopayWebhookController;

// Communication Controllers
use App\Http\Controllers\Communication\ConversationController;
use App\Http\Controllers\Communication\MessageController;

// Reviews Controllers
use App\Http\Controllers\Reviews\ReviewController;

// AI Gateway Controllers
use App\Http\Controllers\AiGateway\AssistantController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Mangopay Webhooks
Route::post('webhooks/mangopay', [MangopayWebhookController::class, 
'handle']);

use App\Http\Controllers\Identity\AuthController;

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/

// Public Auth Routes
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // Protected Auth Routes
        Route::post('auth/logout', [AuthController::class, 'logout']);
        
        // Device Sessions
        Route::get('/auth/sessions', [\App\Http\Controllers\Api\V1\Identity\DeviceSessionController::class, 'index']);
        Route::delete('/auth/sessions/{tokenId}', [\App\Http\Controllers\Api\V1\Identity\DeviceSessionController::class, 'destroy']);
        
        // Signed URLs (For KYC/Avatars)
        Route::post('storage/signed-url', [\App\Http\Controllers\Profile\SignedUrlController::class, 'store']);
        
        // Profile Domain
        Route::apiResource('profiles', 
SitterProfileController::class)->only(['store', 'show']);
        Route::post('profiles/{profile}/avatar', [SitterProfileController::class, 'uploadAvatar']);
        Route::post('profiles/{profile}/certifications', [\App\Http\Controllers\Profile\CertificationController::class, 'store']);

        // Pets Domain
        Route::apiResource('pets', PetController::class);
        Route::post('pets/{petId}/vaccinations', [PetController::class, 'logVaccination']);

        // Marketplace Domain
        Route::get('marketplace/demands/feed', [DemandController::class, 
'index']);
        Route::post('marketplace/demands', [DemandController::class, 
'store']);
        Route::post('marketplace/demands/{demandId}/bids', 
[BidController::class, 'store']);
        Route::get('marketplace/demands/{demandId}/bids', 
[BidController::class, 'index']);
        Route::post('marketplace/bids/{bidId}/accept', 
[BidController::class, 'accept']);
        Route::delete('marketplace/bookings/{bookingId}', 
[BookingController::class, 'destroy']);
        Route::get('marketplace/bookings/{bookingId}', 
[BookingController::class, 'show']);

        // Payments Domain
        Route::get('payments/wallets', [WalletController::class, 'show']);
        Route::post('payments/wallets', [WalletController::class, 
'store']);
        Route::post('payments/wallets/{walletId}/kyc', 
[WalletController::class, 'submitKyc']);
        Route::get('payments/wallets/{walletId}/transactions', 
[TransactionController::class, 'index']);
        Route::post('payments/wallets/{walletId}/payins', 
[TransactionController::class, 'payIn']);

        // Communication Domain
        Route::get('communication/conversations', [ConversationController::class, 'index']);
        Route::post('communication/conversations', [ConversationController::class, 'store']);
        Route::get('communication/conversations/{conversationId}', [ConversationController::class, 'show']);
        Route::get('communication/conversations/{conversationId}/messages', [MessageController::class, 'index']);
        Route::post('communication/conversations/{conversationId}/messages', [MessageController::class, 'store']);
        Route::patch('communication/conversations/{conversationId}/messages/{messageId}/read', [MessageController::class, 'markAsRead']);

        // Reviews Domain
        Route::get('users/{userId}/reviews', [ReviewController::class, 'index']);
        Route::post('reviews/bookings/{bookingId}', [ReviewController::class, 'store']);

        // Communication Domain
        Route::get('conversations', [\App\Http\Controllers\Api\V1\Communication\ConversationController::class, 'index']);
        Route::post('conversations', [\App\Http\Controllers\Api\V1\Communication\ConversationController::class, 'store']);
        Route::get('conversations/{id}', [\App\Http\Controllers\Api\V1\Communication\ConversationController::class, 'show']);
        Route::post('conversations/{id}/messages', [\App\Http\Controllers\Api\V1\Communication\MessageController::class, 'store']);

        // AI Gateway Domain
        Route::get('ai/assistant/history', [AssistantController::class, 'index']);
        Route::post('ai/assistant/ask', [AssistantController::class, 'ask']);
        
    });
});

use App\Http\Controllers\Backoffice\Identity\UserController as BackofficeUserController;
use App\Http\Controllers\Backoffice\Marketplace\DisputeController;
use App\Http\Controllers\Backoffice\Moderation\ContentController;
use App\Http\Controllers\Backoffice\Profile\VerificationController;

/*
|--------------------------------------------------------------------------
| Backoffice (Admin) Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/backoffice')->middleware(['auth:sanctum', 'role:super-admin|admin|moderator'])->group(function () {
    
    // Identity Management
    Route::get('users', [BackofficeUserController::class, 'index']);
    Route::get('users/{id}', [BackofficeUserController::class, 'show']);
    Route::patch('users/{id}/status', [BackofficeUserController::class, 'updateStatus'])->middleware('role:super-admin|admin');
    Route::get('audit-logs', [BackofficeUserController::class, 'auditLogs'])->middleware('role:super-admin|admin');

    // Profile & KYC Verification
    Route::get('verifications/pending', [VerificationController::class, 'pending']);
    Route::get('verifications/{id}/documents', [VerificationController::class, 'generateDocumentUrl']);
    Route::post('verifications/{id}/approve', [VerificationController::class, 'approve']);
    Route::post('verifications/{id}/reject', [VerificationController::class, 'reject']);

    // Marketplace & Payments
    Route::get('bookings', [DisputeController::class, 'index']);
    Route::post('bookings/{id}/cancel', [DisputeController::class, 'cancel'])->middleware('role:super-admin|admin');
    Route::post('bookings/{id}/refund', [DisputeController::class, 'refund'])->middleware('role:super-admin');

    // Moderation (Reviews & Conversations)
    Route::get('moderation/reviews/flagged', [ContentController::class, 'flaggedReviews']);
    Route::delete('moderation/reviews/{id}', [ContentController::class, 'deleteReview']);
    Route::post('moderation/reviews/{id}/ignore', [ContentController::class, 'ignoreReviewFlag']);

    Route::get('moderation/messages/flagged', [ContentController::class, 'flaggedMessages']);
    Route::get('moderation/conversations/{id}', [ContentController::class, 'viewConversation']);
    Route::delete('moderation/messages/{id}', [ContentController::class, 'deleteMessage']);
    Route::post('moderation/messages/{id}/ignore', [ContentController::class, 'ignoreMessageFlag']);
});
