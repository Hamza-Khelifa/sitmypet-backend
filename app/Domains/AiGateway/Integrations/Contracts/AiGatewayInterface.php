<?php

declare(strict_types=1);

namespace App\Domains\AiGateway\Integrations\Contracts;

interface AiGatewayInterface
{
    /**
     * Ask the virtual assistant a question.
     */
    public function askAssistant(string $prompt, array $context = []): string;

    /**
     * Determine if a piece of text is toxic or inappropriate.
     * Returns true if flagged, false if clean.
     */
    public function moderateContent(string $text): bool;
}
