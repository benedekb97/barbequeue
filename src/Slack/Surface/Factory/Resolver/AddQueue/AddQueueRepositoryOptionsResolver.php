<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\AddQueue;

use App\Entity\Repository;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;

class AddQueueRepositoryOptionsResolver extends AbstractAddQueueOptionsResolver implements OptionsResolverInterface
{
    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUE_REPOSITORIES;
    }

    public function resolve(): array
    {
        if (null === $this->workspace) {
            return [];
        }

        return $this->workspace->getRepositories()->map(function (Repository $repository) {
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
