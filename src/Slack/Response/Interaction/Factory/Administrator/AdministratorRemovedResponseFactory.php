<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Administrator;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class AdministratorRemovedResponseFactory
{
    public function create(string $userId): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(sprintf('<@%s> has been removed as an administrator.', $userId)),
            ],
        );
    }
}
