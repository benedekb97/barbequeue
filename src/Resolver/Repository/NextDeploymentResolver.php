<?php

declare(strict_types=1);

namespace App\Resolver\Repository;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Filter\Deployment\DeploymentFilterInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class NextDeploymentResolver
{
    public function __construct(
        /** @var DeploymentFilterInterface[] $filters */
        #[AutowireIterator(DeploymentFilterInterface::TAG)]
        private iterable $filters,
    ) {
    }

    public function resolve(Repository $repository): ?Deployment
    {
        if ($repository->isBlockedByDeployment()) {
            return null;
        }

        $deployments = $repository->getSortedDeploymentsIncludingBlockedRepositories();

        if (empty($deployments)) {
            return null;
        }

        foreach ($this->filters as $filter) {
            $deployments = $filter->filter($deployments);
        }

        if (empty($deployments)) {
            return null;
        }

        return reset($deployments);
    }
}
