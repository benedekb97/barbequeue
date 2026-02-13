<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class RepositoryRemovedResponseFactory
{
    public function create(?string $name): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(sprintf('`%s` has been removed from your repositories.', $name)),
            ],
            replaceOriginal: true
        );
    }
}
