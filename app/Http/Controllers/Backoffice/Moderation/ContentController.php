<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backoffice\Moderation;

use App\Domains\Communication\Entities\Conversation;
use App\Domains\Communication\Entities\Message;
use App\Domains\Reviews\Entities\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    /**
     * List flagged reviews.
     */
    public function flaggedReviews(): JsonResponse
    {
        $reviews = Review::with(['reviewer', 'reviewee'])
            ->where('is_flagged', true)
            ->paginate(20);

        return response()->json($reviews);
    }

    /**
     * List flagged messages.
     */
    public function flaggedMessages(): JsonResponse
    {
        $messages = Message::with(['sender', 'conversation'])
            ->where('is_flagged', true)
            ->paginate(20);

        return response()->json($messages);
    }

    /**
     * View a specific conversation for context around flagged messages.
     */
    public function viewConversation(string $conversationId): JsonResponse
    {
        $conversation = Conversation::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }, 'users:id,email'])->findOrFail($conversationId);

        return response()->json($conversation);
    }

    /**
     * Soft delete a toxic review.
     */
    public function deleteReview(string $reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);
        $review->delete(); // Soft delete assumed or hard delete depending on config

        return response()->json(['message' => 'Review successfully deleted.']);
    }

    /**
     * Delete a toxic message and optionally suspend the conversation.
     */
    public function deleteMessage(string $messageId): JsonResponse
    {
        $message = Message::findOrFail($messageId);
        $message->delete();

        return response()->json(['message' => 'Message successfully deleted.']);
    }

    /**
     * Mark review AI flag as false positive.
     */
    public function ignoreReviewFlag(string $reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);
        $review->is_flagged = false;
        $review->save();

        return response()->json(['message' => 'Review flag ignored.']);
    }

    /**
     * Mark message AI flag as false positive.
     */
    public function ignoreMessageFlag(string $messageId): JsonResponse
    {
        $message = Message::findOrFail($messageId);
        $message->is_flagged = false;
        $message->save();

        return response()->json(['message' => 'Message flag ignored.']);
    }
}
