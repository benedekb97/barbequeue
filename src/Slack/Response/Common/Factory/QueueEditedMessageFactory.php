<?php

declare(strict_types=1);

namespace App\Slack\Response\Common\Factory;

use App\Entity\Queue;
use App\Slack\Response\Common\SlackPrivateMessageResponse;

class QueueEditedMessageFactory
{
    public function create(Queue $queue, string $userId): SlackPrivateMessageResponse
    {
        return new SlackPrivateMessageResponse(
            $userId,
            'Queue '.$queue->getName().' edited successfully',
            [

            ]
        );
    }
}
