<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\BlockElement;

use App\Entity\Queue;
use App\Slack\BlockElement\Component\ButtonBlockElement;

readonly class EditQueueButtonFactory
{
    public function create(Queue $queue): ButtonBlockElement
    {
        return new ButtonBlockElement(
            'Edit',
            'edit-queue-action-'.$queue->getId(),
            value: (string) $queue->getId(),
        );
    }
}
