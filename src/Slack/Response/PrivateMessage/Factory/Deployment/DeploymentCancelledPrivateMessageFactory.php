<?php

declare(strict_types=1);

namespace App\Slack\Response\PrivateMessage\Factory\Deployment;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\PrivateMessage\SlackPrivateMessage;

readonly class DeploymentCancelledPrivateMessageFactory
{
    public function create(Deployment $deployment, Workspace $workspace, Repository $repository, ?User $user): SlackPrivateMessage
    {
        return new SlackPrivateMessage(
            $user,
            $workspace,
            $message = $this->getMessage($deployment, $repository),
            [
                new SectionBlock($message),
            ],
        );
    }

    private function getMessage(Deployment $deployment, Repository $repository): string
    {
        return sprintf(
            '%s\'s deployment of `%s` to `%s` has been cancelled!',
            $deployment->getUserLink(),
            $deployment->getDescription(),
            $repository->getName(),
        );
    }
}
