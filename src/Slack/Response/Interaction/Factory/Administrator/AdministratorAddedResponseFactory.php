<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Administrator;

use App\Entity\Administrator;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class AdministratorAddedResponseFactory
{
    public function create(Administrator $administrator): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(
                    sprintf('%s has been added as an administrator.', $administrator->getUserLink())
                ),
            ]
        );
    }
}
