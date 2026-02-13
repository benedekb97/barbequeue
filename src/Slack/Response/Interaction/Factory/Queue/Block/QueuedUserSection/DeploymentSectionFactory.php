<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUserSection;

use App\Entity\Deployment;
use App\Slack\Block\Component\SectionBlock;

readonly class DeploymentSectionFactory
{
    public function create(Deployment $deployment, ?int $place = null): SectionBlock
    {
        return new SectionBlock(sprintf(
            '%s%s deploying `%s` to `%s`. <%s|See more>%s%s',
            null !== $place ? "*#$place* - " : '',
            $deployment->getUserLink(),
            $deployment->getDescription(),
            $deployment->getRepository()?->getName() ?? '',
            $deployment->getLink(),
            ($expiry = $deployment->getExpiryMinutesLeft()) === null
                ? ''
                : ' - _Reserved for '.$expiry.' minutes._',
            1 === $place && ($blocker = $deployment->getBlocker()) !== null
                ? ' - _Blocked by '.$blocker->getUserLink().' in the `'.$blocker->getQueue()?->getName().'` queue._'
                : '',
        ));
    }
}
