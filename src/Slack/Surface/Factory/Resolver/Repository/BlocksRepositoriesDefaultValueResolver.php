<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Repository;

use App\Entity\Repository;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\DefaultValueResolverInterface;

class BlocksRepositoriesDefaultValueResolver extends AbstractRepositoryDefaultValueResolver implements DefaultValueResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::REPOSITORY_BLOCKS;
    }

    public function resolveString(): ?string
    {
        return null;
    }

    public function resolveArray(): ?array
    {
        if (null === $this->repository) {
            return [];
        }

        return $this->repository->getDeploymentBlocksRepositories()->map(function (Repository $repository) {
            return [
                'text' => [
                    'type' => 'plain_text',
                    'text' => (string) $repository->getName(),
                ],
                'value' => (string) $repository->getId(),
            ];
        })->toArray();
    }
}
