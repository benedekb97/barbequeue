<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Component\Exception;

use App\Slack\Interaction\InteractionType;
use Exception;

class UnhandledInteractionTypeException extends Exception
{
    public function __construct(private readonly InteractionType $interactionType)
    {
        parent::__construct();
    }

    public function getInteractionType(): InteractionType
    {
        return $this->interactionType;
    }
}
