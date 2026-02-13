<?php

declare(strict_types=1);

namespace App\Slack\Surface\Factory\Resolver\LeaveQueue;

use App\Entity\Queue;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Option\QueuedUserOptionFactory;
use App\Slack\Surface\Factory\Resolver\OptionsResolverInterface;

class LeaveQueueOptionsResolver implements OptionsResolverInterface
{
    private ?Queue $queue = null;

    private ?string $userId = null;

    public function __construct(
        private readonly QueuedUserOptionFactory $queuedUserOptionFactory,
    ) {
    }

    public function setQueue(?Queue $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function setUserId(?string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getSupportedArgument(): ModalArgument
    {
        return ModalArgument::QUEUED_USER_ID;
    }

    public function resolve(): array
    {
        $options = [];

        if (null === $this->queue || null === $this->userId) {
            return $options;
        }

        $place = 0;

        foreach ($this->queue->getSortedUsers() as $queuedUser) {
            ++$place;

            if ($queuedUser->getUser()?->getSlackId() === $this->userId) {
                $options[] = $this->queuedUserOptionFactory->create($queuedUser, $place);
            }
        }

        return $options;
    }
}
