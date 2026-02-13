<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Factory\Deployment;

use App\Entity\Deployment;
use App\Entity\User;
use App\Entity\Workspace;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\BlockElement\Component\ButtonBlockElement;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;

readonly class DeploymentAddedPrivateMessageFactory
{
    public function create(Deployment $deployment, Workspace $workspace, ?User $user): SlackPrivateMessage
    {
        return new SlackPrivateMessage(
            $user,
            $workspace,
            $message = $this->getMessage($deployment),
            [
                new SectionBlock(
                    $message,
                    accessory: new ButtonBlockElement(
                        'More information',
                        'more-info',
                        $deployment->getLink(),
                    )
                ),
            ],
        );
    }

    private function getMessage(Deployment $deployment): string
    {
        /** @var Deployment $blocker */
        $blocker = $deployment->getBlocker();

        return sprintf(
            '%s joined the `%s` queue to deploy `%s` to `%s`. They are %s in the queue and have to wait for %s to finish deploying to `%s` before they can start.',
            $deployment->getUserLink(),
            $deployment->getQueue()?->getName(),
            $deployment->getDescription(),
            $deployment->getRepository()?->getName(),
            $deployment->getPlacement(),
            $blocker->getUserLink(),
            $blocker->getRepository()?->getName(),
        );
    }
}
