<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class ConfigurationSavedResponseFactory
{
    public function create(): SlackInteractionResponse
    {
        return new SlackInteractionResponse([
            new SectionBlock('Your preferences have been saved.'),
        ]);
    }
}
