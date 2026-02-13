<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Factory\Deployment;

use App\Entity\Deployment;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;

readonly class StartDeploymentMessageFactory
{
    public function create(Deployment $deployment): SlackPrivateMessage
    {
        return new SlackPrivateMessage(
            $deployment->getUser(),
            $deployment->getQueue()?->getWorkspace(),
            $message = $this->getMessage($deployment),
            [
                new SectionBlock($message),
            ]
        );
    }

    private function getMessage(Deployment $deployment): string
    {
        if (null !== $deployment->getExpiresAt()) {
            return sprintf(
                'You can start deploying `%s` to `%s` now! You have `%d minutes` before you are removed from the front of the queue.',
                $deployment->getDescription(),
                $deployment->getRepository()?->getName(),
                $deployment->getExpiresAt()->diffInMinutes(absolute: true),
            );
        }

        return sprintf(
            'You can start deploying `%s` to `%s` now!',
            $deployment->getDescription(),
            $deployment->getRepository()?->getName()
        );
    }
}
