<?php

declare(strict_types=1);

namespace App\Event\Deployment;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Entity\Workspace;

readonly class DeploymentCancelledEvent extends DeploymentEvent
{
    public function __construct(
        Deployment $deployment,
        Workspace $workspace,
        private Repository $repository,
        bool $notifyOwner = false,
    ) {
        parent::__construct($deployment, $workspace, $notifyOwner);
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }
}
