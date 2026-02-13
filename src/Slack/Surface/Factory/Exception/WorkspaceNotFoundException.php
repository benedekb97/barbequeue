<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Exception;

class WorkspaceNotFoundException extends \Exception
{
    public function __construct(
        private readonly string $workspaceId,
    ) {
        parent::__construct();
    }

    public function getWorkspaceId(): string
    {
        return $this->workspaceId;
    }
}
