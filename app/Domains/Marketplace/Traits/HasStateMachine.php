<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Traits;

use App\Domains\Marketplace\Exceptions\InvalidStateException;

trait HasStateMachine
{
    /**
     * Define the state machine property name. Defaults to 'status'.
     */
    protected function getStateMachineProperty(): string
    {
        return 'status';
    }

    /**
     * Transition the model to a new state.
     *
     * @param string|\BackedEnum $newState
     * @throws InvalidStateException
     */
    public function transitionTo(mixed $newState): void
    {
        $property = $this->getStateMachineProperty();
        $currentState = $this->{$property};
        
        // Extract values if Enums are used
        $currentValue = $currentState instanceof \BackedEnum ? $currentState->value : (string) $currentState;
        $newValue = $newState instanceof \BackedEnum ? $newState->value : (string) $newState;

        // If no state is set yet and we allow initialization
        if (empty($currentValue)) {
             $this->{$property} = $newState;
             return;
        }

        if ($currentValue === $newValue) {
            return; // No transition needed
        }

        $allowedTransitions = $this->getAllowedTransitions();

        if (!isset($allowedTransitions[$currentValue]) || !in_array($newValue, $allowedTransitions[$currentValue], true)) {
            throw InvalidStateException::transitionNotAllowed(static::class, $currentValue, $newValue);
        }

        $this->{$property} = $newState;
    }

    /**
     * Must be implemented by the model.
     * Returns an array mapping state to an array of allowed next states.
     * [ 'open' => ['assigned', 'cancelled'] ]
     *
     * @return array<string, array<string>>
     */
    abstract protected function getAllowedTransitions(): array;
}
