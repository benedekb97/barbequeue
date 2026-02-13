<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Administrator;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class UnauthorisedResponseFactory
{
    public function create(): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock('You are not allowed to do that!'),
            ]
        );
    }
}
