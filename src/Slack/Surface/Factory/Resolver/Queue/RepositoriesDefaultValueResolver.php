<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\Queue;

use App\Entity\DeploymentQueue;
use App\Entity\Repository;
use App\Slack\Surface\Component\ModalArgument;

class RepositoriesDefaultValueResolver extends AbstractQueueDefaultValueResolver
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUE_REPOSITORIES;
    }

    public function resolveString(): ?string
    {
        return null;
    }

    public function resolveArray(): ?array
    {
        if (!$this->queue instanceof DeploymentQueue) {
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
