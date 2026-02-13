<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Entity\Repository;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class RepositoryAddedResponseFactory
{
    public function create(Repository $repository): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(
                    sprintf('Repository `%s` has been added to your workspace!', $repository->getName())
                ),
            ],
        );
    }
}
