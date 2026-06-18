<?php

declare(strict_types=1);

namespace App\Http\Controllers\AiGateway;

use App\Domains\AiGateway\Actions\AskVirtualAssistantAction;
use App\Domains\AiGateway\DTOs\AskAssistantDTO;
use App\Domains\AiGateway\Repositories\Contracts\AiInteractionRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __construct(
        private readonly AiInteractionRepositoryInterface $interactionRepository,
        private readonly AskVirtualAssistantAction $askAssistantAction
    ) {}

    public function index(Request $request): JsonResponse
    {
        $history = $this->interactionRepository->getHistoryForUser($request->user()->id);
        
        return response()->json(['data' => $history]);
    }

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:1000'],
            'context' => ['nullable', 'array']
        ]);

        $dto = new AskAssistantDTO(
            userId: $request->user()->id,
            prompt: $validated['prompt'],
            context: $validated['context'] ?? []
        );

        $response = $this->askAssistantAction->execute($dto);

        return response()->json([
            'message' => 'Assistant replied successfully.',
            'data' => [
                'prompt' => $dto->prompt,
                'response' => $response,
            ]
        ]);
    }
}
