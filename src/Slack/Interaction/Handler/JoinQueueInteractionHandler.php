<?php

declare(strict_types=1);

namespace App\Slack\Interaction\Handler;

use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\QueueManager;
use App\Slack\Interaction\Component\SlackInteraction;
use App\Slack\Interaction\Interaction;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\QueueJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\UnableToJoinQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\UnrecognisedQueueResponseFactory;

readonly class JoinQueueInteractionHandler implements SlackInteractionHandlerInterface
{
    public function __construct(
        private QueueManager $queueManager,
        private QueueJoinedResponseFactory $queueJoinedResponseFactory,
        private UnableToJoinQueueResponseFactory $unableToJoinQueueResponseFactory,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
    ) {
    }

    public function supports(SlackInteraction $interaction): bool
    {
        return $interaction->getInteraction() === Interaction::JOIN_QUEUE;
    }

    public function handle(SlackInteraction $interaction): void
    {
        try {
            $queue = $this->queueManager->joinQueue(
                $interaction->getValue(),
                $interaction->getDomain(),
                $interaction->getUserId(),
            );

            $response = $this->queueJoinedResponseFactory->create($queue, $interaction->getUserId());
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getDomain(),
                $exception->getUserId(),
            );
        } catch (UnableToJoinQueueException $exception) {
            $response = $this->unableToJoinQueueResponseFactory->create($exception->getQueue());
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $interaction->setResponse($response);
        }
    }
}
