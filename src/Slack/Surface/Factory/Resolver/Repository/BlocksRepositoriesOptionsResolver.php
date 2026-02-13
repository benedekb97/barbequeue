<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Repository;

use App\Entity\Repository;
use App\Repository\RepositoryRepositoryInterface;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Exception\NoOptionsAvailableException;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;

class BlocksRepositoriesOptionsResolver extends AbstractRepositoryOptionsResolver implements OptionsResolverInterface
{
    public function __construct(
        private readonly RepositoryRepositoryInterface $repositoryRepository,
    ) {
    }

    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::REPOSITORY_BLOCKS;
    }

    /** @throws NoOptionsAvailableException */
    public function resolve(): array
    {
        if (null === $this->teamId) {
            return [];
        }

        $repositories = $this->repositoryRepository->findByTeamId($this->teamId);

        if (empty($repositories)) {
            throw new NoOptionsAvailableException();
        }

        return array_map(function (Repository $repository) {
            return [
                'text' => [
                    'type' => 'plain_text',
                    'text' => (string) $repository->getName(),
                ],
                'value' => (string) $repository->getId(),
            ];
        }, $repositories);
    }
}
