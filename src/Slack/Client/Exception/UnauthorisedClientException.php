<?php

declare(strict_types=1);

namespace App\Slack\Client\Exception;

use App\Entity\Workspace;

class UnauthorisedClientException extends \Exception
{
    public function __construct(
        private readonly ?Workspace $workspace,
    ) {
        parent::__construct('Could not resolve bot token for workspace '.($workspace?->getName() ?? ''));
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }
}
