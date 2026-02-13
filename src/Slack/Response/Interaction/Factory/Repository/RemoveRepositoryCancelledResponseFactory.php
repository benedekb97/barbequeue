<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class RemoveRepositoryCancelledResponseFactory
{
    public function create(): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock('Operation cancelled.'),
            ],
            replaceOriginal: true
        );
    }
}
