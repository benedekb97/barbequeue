<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository\Deployments;

use App\Entity\Repository;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Repository\Deployments\Block\RepositoryDeploymentSectionFactory;

readonly class RepositoryDeploymentsSectionsFactory
{
    public function __construct(
        private RepositoryDeploymentSectionFactory $deploymentSectionFactory,
    ) {
    }

    /** @return SectionBlock[] */
    public function create(Repository $repository): array
    {
        $blocks = [];

        if (0 === $repository->getDeployments()->count()) {
            return [
                new SectionBlock(sprintf('`%s` is not being deployed to.', $repository->getName())),
            ];
        }

        $blocks[] = new SectionBlock(sprintf('Users deploying to `%s`:', $repository->getName()));

        $place = 0;

        foreach ($repository->getSortedDeployments() as $deployment) {
            $blocks[] = $this->deploymentSectionFactory->create($deployment, ++$place);
        }

        return $blocks;
    }
}
