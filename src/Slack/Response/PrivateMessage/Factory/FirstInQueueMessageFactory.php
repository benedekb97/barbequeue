<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Factory;

use App\Entity\QueuedUser;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;
use Carbon\CarbonImmutable;

class FirstInQueueMessageFactory
{
    public function create(QueuedUser $queuedUser): SlackPrivateMessage
    {
        return new SlackPrivateMessage(
            $queuedUser->getUser(),
            $queuedUser->getQueue()?->getWorkspace(),
            $message = $this->getMessage($queuedUser),
            [
                new SectionBlock($message),
            ],
        );
    }

    private function getMessage(QueuedUser $queuedUser): string
    {
        if (($expiresAt = $queuedUser->getExpiresAt()) === null) {
            return 'You are now first in the `'.$queuedUser->getQueue()?->getName().'` queue!';
        }

        return sprintf(
            'You are now first in the `%s` queue! You will be removed automatically after %d minutes.',
            $queuedUser->getQueue()?->getName() ?? '',
            CarbonImmutable::now()->diffInMinutes($expiresAt)
        );
    }
}
