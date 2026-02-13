<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Administrator;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class AdministratorNotFoundResponseFactory
{
    public function create(string $userId): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(sprintf('<@%s> is not an administrator.', $userId)),
            ],
        );
    }
}
