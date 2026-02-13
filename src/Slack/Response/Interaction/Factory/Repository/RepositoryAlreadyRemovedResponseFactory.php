<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class RepositoryAlreadyRemovedResponseFactory
{
    public function create(): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock('The repository you are trying to remove does not exist. It may have been removed by someone else.'),
            ],
            replaceOriginal: true,
        );
    }
}
