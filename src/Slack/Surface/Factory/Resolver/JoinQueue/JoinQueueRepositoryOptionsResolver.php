<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\JoinQueue;

use App\Entity\DeploymentQueue;
use App\Entity\Repository;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;

class JoinQueueRepositoryOptionsResolver implements OptionsResolverInterface
{
    private ?DeploymentQueue $queue = null;

    public function setQueue(?DeploymentQueue $queue): void
    {
        $this->queue = $queue;
    }

    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::DEPLOYMENT_REPOSITORY;
    }

    public function resolve(): array
    {
        if (null === $this->queue) {
            return [];
        }

        return $this->queue->getRepositories()->map(function (Repository $repository) {
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
