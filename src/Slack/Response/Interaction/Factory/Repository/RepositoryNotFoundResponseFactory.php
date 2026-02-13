<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class RepositoryNotFoundResponseFactory
{
    public function create(): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock('We could not find the repository you are trying to edit. It may have been deleted by another administrator while you were editing it.'),
            ]
        );
    }
}
