<?php

declare(strict_types=1);

namespace App\Domains\AiGateway\Actions;

use App\Domains\AiGateway\DTOs\AskAssistantDTO;
use App\Domains\AiGateway\Entities\AiInteraction;
use App\Domains\AiGateway\Integrations\Contracts\AiGatewayInterface;

final class AskVirtualAssistantAction
{
    public function __construct(
        private readonly AiGatewayInterface $aiGateway,
        private readonly ModerateContentAction $moderateContentAction
    ) {}

    public function execute(AskAssistantDTO $dto): string
    {
        // 1. Pre-Moderate the prompt
        $isFlagged = $this->moderateContentAction->execute(new \App\Domains\AiGateway\DTOs\ModerateContentDTO($dto->prompt, $dto->userId));
        
        if ($isFlagged) {
            // Fast-fail if malicious intent is detected
            return "I'm sorry, but I cannot fulfill that request as it violates our safety guidelines.";
        }

        // 2. Get Response from AI
        $response = $this->aiGateway->askAssistant($dto->prompt, $dto->context);

        // 2. Audit Log the interaction
        AiInteraction::create([
            'user_id' => $dto->userId,
            'type' => 'assistant',
            'prompt' => $dto->prompt,
            'response' => $response,
            'is_flagged' => false,
        ]);

        return $response;
    }
}
