<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class GenericFailureResponseFactory
{
    public function create(): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock('Something went wrong.'),
        ]);
    }
}
