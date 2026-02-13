<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\PopQueue;

use App\Entity\Queue;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Option\QueuedUserOptionFactory;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;

class PopQueueOptionsResolver implements OptionsResolverInterface
{
    private ?Queue $queue = null;

    public function __construct(
        private readonly QueuedUserOptionFactory $queuedUserOptionFactory,
    ) {
    }

    public function setQueue(?Queue $queue): void
    {
        $this->queue = $queue;
    }

    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUED_USER_ID;
    }

    public function resolve(): array
    {
        if (null === $this->queue) {
            return [];
        }

        $options = [];

        $sortedUsers = $this->queue->getSortedUsers();
        $place = 0;

        foreach ($sortedUsers as $user) {
            $options[] = $this->queuedUserOptionFactory->create($user, ++$place);
        }

        return $options;
    }
}
