<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class RepositoryAlreadyExistsResponseFactory
{
    public function create(string $name): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(sprintf('A repository called `%s` already exists!', $name)),
            ],
        );
    }
}
