<?php

declare(strict_types=1);

namespace App\Domains\AiGateway\Actions;

use App\Domains\AiGateway\DTOs\ModerateContentDTO;
use App\Domains\AiGateway\Entities\AiInteraction;
use App\Domains\AiGateway\Integrations\Contracts\AiGatewayInterface;

final class ModerateContentAction
{
    public function __construct(
        private readonly AiGatewayInterface $aiGateway
    ) {}

    public function execute(ModerateContentDTO $dto): bool
    {
        // 1. Check with AI Gateway
        $isFlagged = $this->aiGateway->moderateContent($dto->content);

        // 2. Audit Log
        AiInteraction::create([
            'user_id' => $dto->userId,
            'type' => 'moderation',
            'prompt' => $dto->content,
            'response' => $isFlagged ? 'FLAGGED' : 'CLEAN',
            'is_flagged' => $isFlagged,
        ]);

        return $isFlagged;
    }
}
