<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Factory\Deployment;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;

readonly class CompletedDeploymentMessageFactory
{
    public function create(Deployment $deployment, Workspace $workspace, Repository $repository): SlackPrivateMessage
    {
        return new SlackPrivateMessage(
            $deployment->getUser(),
            $workspace,
            $message = $this->getMessage($deployment, $repository),
            [
                new SectionBlock($message),
            ],
        );
    }

    private function getMessage(Deployment $deployment, Repository $repository): string
    {
        if (($expiry = $deployment->getExpiryMinutes()) !== null) {
            return sprintf(
                'Your deployment of `%s` to `%s` has completed automatically after %d minutes.',
                $deployment->getDescription(),
                $repository->getName(),
                $expiry,
            );
        }

        return sprintf(
            'Your deployment of `%s` to `%s` has been marked as completed.',
            $deployment->getDescription(),
            $repository->getName(),
        );
    }
}
