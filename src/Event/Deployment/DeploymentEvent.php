<?php

declare(strict_types=1);

namespace App\Event\Deployment;

use App\Entity\Deployment;
use App\Entity\Workspace;

readonly class DeploymentEvent
{
    public function __construct(
        private Deployment $deployment,
        private Workspace $workspace,
        private bool $notifyOwner = false,
    ) {
    }

    public function getDeployment(): Deployment
    {
        return $this->deployment;
    }

    public function getWorkspace(): Workspace
    {
        return $this->workspace;
    }

    public function shouldNotifyOwner(): bool
    {
        return $this->notifyOwner;
    }
}
