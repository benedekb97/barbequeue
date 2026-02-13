<?php

declare(strict_types=1);

namespace App\Filter\Deployment;

use App\Entity\Deployment;
use App\Entity\DeploymentQueue;
use Psr\Log\LoggerInterface;

readonly class DeploymentQueueFilter implements DeploymentFilterInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /** @param Deployment[] $deployments */
    public function filter(array $deployments): array
    {
        $filtered = [];

        $this->logger->debug('filtering deployments for repository');

        foreach ($deployments as $deployment) {
            /** @var DeploymentQueue $queue */
            $queue = $deployment->getQueue();

            if ($queue->isDeploymentAllowed($deployment)) {
                $this->logger->debug('queue returned deployment allowed.');

                $filtered[] = $deployment;
            }
        }

        return $filtered;
    }
}
