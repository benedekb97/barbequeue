<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Entity\Queue;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Block\Component\TableBlock;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use Carbon\CarbonImmutable;

readonly class ListQueuedUsersResponseFactory
{
    public function create(Queue $queue): SlackInteractionResponse
    {
        return new SlackInteractionResponse(array_filter([
            new HeaderBlock('Users currently in the '.$queue->getName().' queue'),
            new DividerBlock(),
            new TableBlock($this->getRows($queue)),
            $this->getExtraInformationBlock($queue),
        ]));
    }

    private function getRows(Queue $queue): array
    {
        return [
            $this->getHeaderRow(),
            ...$this->getDataRows($queue),
        ];
    }

    private function getHeaderRow(): array
    {
        return [
            [
                'type' => 'raw_text',
                'text' => '#',
            ], [
                'type' => 'raw_text',
                'text' => 'User',
            ],
        ];
    }

    private function getDataRows(Queue $queue): iterable
    {
        $place = 1;

        foreach ($queue->getSortedUsers() as $user) {
            yield [
                [
                    'type' => 'raw_text',
                    'text' => "$place",
                ],[
                    'type' => 'rich_text',
                    'elements' => [
                        [
                            'type' => 'rich_text_section',
                            'elements' => [
                                [
                                    'type' => 'user',
                                    'user_id' => $user->getUserId(),
                                ]
                            ]
                        ]
                    ],
                ]
            ];

            $place++;
        }
    }

    private function getExtraInformationBlock(Queue $queue): ?SectionBlock
    {
        if ($queue->getExpiryMinutes() !== null) {
            return new SectionBlock(
                sprintf(
                    '%s has %d minutes left at the front of the queue.',
                    $queue->getFirstPlace()->getUserLink(),
                    $queue->getFirstPlace()->getExpiresAt()->diffInMinutes(CarbonImmutable::now())
                )
            );
        }

        return null;
    }
}
