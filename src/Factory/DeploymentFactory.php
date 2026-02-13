<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;

class DeploymentFactory
{
    public function createForDeploymentQueue(DeploymentQueue $deploymentQueue): Deployment
    {
        $deployment = new Deployment()->setCreatedAtNow();

        $deploymentQueue->addQueuedUser($deployment);

        return $deployment;
    }
}
