<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Factory\Deployment;

use App\Entity\Deployment;
use App\Entity\User;
use App\Entity\Workspace;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\BlockElement\Component\ButtonBlockElement;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;

readonly class DeploymentStartedPrivateMessageFactory
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
                        'more-information-button',
                        $deployment->getLink(),
                    )
                ),
            ],
        );
    }

    private function getMessage(Deployment $deployment): string
    {
        return sprintf(
            '%s has started their deployment of `%s` to `%s`',
            $deployment->getUserLink(),
            $deployment->getDescription(),
            $deployment->getRepository()?->getName(),
        );
    }
}
