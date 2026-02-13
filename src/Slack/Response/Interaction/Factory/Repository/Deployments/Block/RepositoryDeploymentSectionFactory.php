<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository\Deployments\Block;

use App\Entity\Deployment;
use App\Slack\Block\Component\SectionBlock;

readonly class RepositoryDeploymentSectionFactory
{
    public function create(Deployment $deployment, int $place): SectionBlock
    {
        return new SectionBlock(sprintf(
            '*#%d* - %s deploying `%s` in the `%s` queue. <%s|See more>%s%s',
            $place,
            $deployment->getUserLink(),
            $deployment->getDescription(),
            $deployment->getQueue()?->getName(),
            $deployment->getLink(),
            ($expiry = $deployment->getExpiryMinutesLeft()) !== null
                ? " - _Reserved for $expiry minutes._"
                : '',
            1 === $place && ($blocker = $deployment->getBlocker()) !== null
                ? ' - _Blocked by '.$blocker->getUserLink().' in the `'.$blocker->getQueue()?->getName().'` queue._'
                : '',
        ));
    }
}
