<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Repository;

use App\Entity\Repository;
use App\Slack\Block\Component\ActionsBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\BlockElement\Component\ButtonBlockElement;
use App\Slack\Common\Component\SlackConfirmation;
use App\Slack\Common\Style;
use App\Slack\Response\Interaction\SlackInteractionResponse;

class ConfirmRemoveRepositoryResponseFactory
{
    public function create(Repository $repository): SlackInteractionResponse
    {
        return new SlackInteractionResponse(
            [
                new SectionBlock(
                    sprintf('Are you sure you want to remove the `%s` repository?', $repository->getName()),
                ),
                new ActionsBlock(
                    [
                        new ButtonBlockElement(
                            'Cancel',
                            actionId: 'remove-repository-action-'.$repository->getId().'-cancel',
                            value: 'no',
                        ),
                        new ButtonBlockElement(
                            'Yes, remove it.',
                            'remove-repository-action-'.$repository->getId().'-confirm',
                            value: (string) $repository->getId(),
                            style: Style::DANGER,
                            confirm: new SlackConfirmation(
                                'Are you sure?',
                                'Just double-checking...',
                                'Yes, delete it already',
                                'On second thoughts...',
                                style: Style::DANGER,
                            ),
                        ),
                    ]
                ),
            ]
        );
    }
}
