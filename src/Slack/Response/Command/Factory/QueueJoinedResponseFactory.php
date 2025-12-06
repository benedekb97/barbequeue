<?php

declare(strict_types=1);

namespace App\Slack\Response\Command\Factory;

use App\Entity\Queue;
use App\Slack\Response\Command\SlackCommandResponse;
use App\Slack\Response\Response;

class QueueJoinedResponseFactory
{
    public function create(Queue $queue): SlackCommandResponse
    {
        return new SlackCommandResponse(
            Response::EPHEMERAL,
            'You are now in the '.$queue->getName().' queue.',
        );
    }
}
