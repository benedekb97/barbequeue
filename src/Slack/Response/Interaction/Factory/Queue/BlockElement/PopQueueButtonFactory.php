<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\BlockElement;

use App\Entity\Queue;
use App\Slack\BlockElement\Component\ButtonBlockElement;

readonly class PopQueueButtonFactory
{
    public function create(Queue $queue): ButtonBlockElement
    {
        return new ButtonBlockElement(
            'Pop',
            'pop-queue-action-'.$queue->getId(),
            value: (string) $queue->getName(),
        );
    }
}
