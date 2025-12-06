<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory;

use App\Entity\Queue;
use App\Repository\QueueRepositoryInterface;
use App\Slack\Block\Component\ActionsBlock;
use App\Slack\Block\Component\DividerBlock;
use App\Slack\Block\Component\HeaderBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\BlockElement\Component\ButtonBlockElement;
use App\Slack\Response\Interaction\SlackInteractionResponse;

readonly class UnrecognisedQueueResponseFactory
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
    ) {
    }

    public function create(
        string $queueName,
        string $domain,
        ?string $userId = null,
        bool $withActions = true
    ): SlackInteractionResponse {
        return new SlackInteractionResponse(array_filter([
            new HeaderBlock(sprintf('Queue \'%s\' does not exist.', $queueName)),
            new DividerBlock(),
            new SectionBlock(
                sprintf(
                    "We couldn't find a queue called *%s*.%s",
                    $queueName,
                    $withActions ? ' Try these on for size:' : ''
                ),
            ),
            $withActions ? new ActionsBlock(
                $this->getQueueActions($domain, $userId),
                'unrecognised_queue_action'
            ) : null,
        ]));
    }

    /** @return array|ButtonBlockElement[] */
    private function getQueueActions(string $domain, ?string $userId): array
    {
        $buttons = [];

        if ($userId === null) {
            return $buttons;
        }

        $availableQueues = $this->queueRepository->findBy(['domain' => $domain]);

        /** @var Queue $queue */
        foreach ($availableQueues as $queue) {
            $buttons[] = $queue->canJoin($userId)
                ? $this->createJoinQueueButton($queue)
                : $this->createLeaveQueueButton($queue);
        }

        return $buttons;
    }

    private function createJoinQueueButton(Queue $queue): ButtonBlockElement
    {
        return new ButtonBlockElement(
            'Join '.$queue->getName().' queue',
            'join-queue-'.$queue->getId(),
            value: $queue->getName()
        );
    }

    private function createLeaveQueueButton(Queue $queue): ButtonBlockElement
    {
        return new ButtonBlockElement(
            'Leave '.$queue->getName().' queue',
            'leave-queue-'.$queue->getId(),
            value: $queue->getName(),
        );
    }
}
