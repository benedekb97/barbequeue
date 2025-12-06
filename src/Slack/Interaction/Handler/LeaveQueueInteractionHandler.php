<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler;

use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\QueueManager;
use App\Slack\Interaction\Component\SlackInteraction;
use App\Slack\Interaction\Interaction;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\QueueLeftResponseFactory;
use App\Slack\Response\Interaction\Factory\UnableToLeaveQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\UnrecognisedQueueResponseFactory;

readonly class LeaveQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private QueueManager $queueManager,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private UnableToLeaveQueueResponseFactory $unableToLeaveQueueResponseFactory,
        private QueueLeftResponseFactory $queueLeftResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
    ) {
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return $interaction->getInteraction() === Interaction::LEAVE_QUEUE;
    }

    public function handle(SlackInteraction $interaction): void
    {
        try {
            $queue = $this->queueManager->leaveQueue(
                $interaction->getValue(),
                $interaction->getDomain(),
                $userId = $interaction->getUserId()
            );

            $response = $this->queueLeftResponseFactory->create($queue, $userId);
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getDomain(),
                $exception->getUserId()
            );
        } catch (UnableToLeaveQueueException $exception) {
            $response = $this->unableToLeaveQueueResponseFactory->create($exception->getQueue());
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $interaction->setResponse($response);
        }
    }
}
