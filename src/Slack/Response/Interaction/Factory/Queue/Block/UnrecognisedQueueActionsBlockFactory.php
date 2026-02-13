<?php

declare(strict_types=1);

namespace App\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\Queue;
use App\Repository\QueueRepositoryInterface;
use App\Slack\Block\Component\ActionsBlock;
use App\Slack\BlockElement\Component\ButtonBlockElement;

readonly class UnrecognisedQueueActionsBlockFactory
{
    public function __construct(private QueueRepositoryInterface $queueRepository)
    {
    }

    public function create(string $teamId, ?string $userId): ?ActionsBlock
    {
        if (null === $userId) {
            return null;
        }

        $queues = $this->queueRepository->findByTeamId($teamId);

        if (empty($queues)) {
            return null;
        }

        $buttons = $this->getQueueActions($queues, $userId);

        if (empty($buttons)) {
            return null;
        }

        return new ActionsBlock(
            $buttons,
            'unrecognised_queue_action'
        );
    }

    /** @return array|ButtonBlockElement[] */
    private function getQueueActions(array $queues, string $userId): array
    {
        $buttons = [];

        /** @var Queue $queue */
        foreach ($queues as $queue) {
            if ($queue->canJoin($userId)) {
                $buttons[] = $this->createJoinQueueButton($queue);
            }

            if ($queue->canRelease($userId)) {
                $buttons[] = $this->createReleaseQueueButton($queue);
            }

            if ($queue->canLeave($userId)) {
                $buttons[] = $this->createLeaveQueueButton($queue);
            }
        }

        return $buttons;
    }

    private function createJoinQueueButton(Queue $queue): ButtonBlockElement
    {
        $queueName = $queue->getName();

        return new ButtonBlockElement(
            'Join '.$queueName.' queue',
            'join-queue-'.$queue->getId(),
            value: $queueName,
        );
    }

    private function createLeaveQueueButton(Queue $queue): ButtonBlockElement
    {
        $queueName = $queue->getName();

        return new ButtonBlockElement(
            'Leave '.$queueName.' queue',
            'leave-queue-'.$queue->getId(),
            value: $queueName,
        );
    }

    private function createReleaseQueueButton(Queue $queue): ButtonBlockElement
    {
        $queueName = $queue->getName();

        return new ButtonBlockElement(
            'Release '.$queueName.' queue',
            'release-queue-'.$queue->getId(),
            value: $queueName,
        );
    }
}
