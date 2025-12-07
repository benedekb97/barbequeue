<?php

declare(strict_types=1);

namespace App\Slack\Response\Common\Factory;

use App\Entity\Queue;
use App\Slack\Block\Component\ActionsBlock;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Block\Component\TableBlock;
use App\Slack\BlockElement\Component\ButtonBlockElement;
use App\Slack\Response\Common\SlackPrivateMessageResponse;

class QueueEditedMessageFactory
{
    public function create(Queue $queue, string $userId): SlackPrivateMessageResponse
    {
        return new SlackPrivateMessageResponse(
            $userId,
            text: null,
            blocks: [
                new SectionBlock('Queue *'. $queue->getName().'* edited successfully.'),
                new DividerBlock(),
                new TableBlock([
                    [
                        [
                            'type' => 'raw_text',
                            'text' => 'Parameter',
                        ],[
                            'type' => 'raw_text',
                            'text' => 'Value',
                        ]
                    ],[
                        [
                            'type' => 'raw_text',
                            'text' => 'Top of queue expiry (minutes)',
                        ],[
                            'type' => 'raw_text',
                            'text' => $queue->getExpiryMinutes()
                                ? $queue->getExpiryMinutes() . ' minutes'
                                : 'No expiry',
                        ]
                    ],[
                        [
                            'type' => 'raw_text',
                            'text' => 'Maximum entries per user',
                        ],[
                            'type' => 'raw_text',
                            'text' => $queue->getMaximumEntriesPerUser()
                                ? $queue->getMaximumEntriesPerUser() . ' entries'
                                : 'No limit',
                        ]
                    ]
                ]),
                new ActionsBlock([
                    new ButtonBlockElement(
                        'Edit',
                        'edit-queue-action-'.$queue->getId(),
                        value: (string) $queue->getId(),
                    )
                ]),
            ]
        );
    }
}
