<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection;

use App\Entity\QueuedUser;
use App\Slack\Block\Component\SectionBlock;

readonly class SimpleQueuedUserSectionFactory
{
    public function create(QueuedUser $queuedUser, int $place): SectionBlock
    {
        return new SectionBlock(sprintf(
            '#%d - %s %s',
            $place,
            $queuedUser->getUserLink(),
            ($expiry = $queuedUser->getExpiryMinutesLeft()) === null
                ? ''
                : 'Reserved for '.$expiry.' minutes',
        ));
    }
}
