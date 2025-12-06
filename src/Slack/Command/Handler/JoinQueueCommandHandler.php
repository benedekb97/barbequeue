<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToJoinQueueException;
use App\Service\Queue\QueueManager;
use App\Slack\Command\Command;
use App\Slack\Command\Component\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\GenericFailureResponseFactory;
use App\Slack\Response\Interaction\Factory\QueueJoinedResponseFactory;
use App\Slack\Response\Interaction\Factory\UnableToJoinQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\UnrecognisedQueueResponseFactory;

readonly class JoinQueueCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private QueueManager $queueManager,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private UnableToJoinQueueResponseFactory $unableToJoinQueueResponseFactory,
        private QueueJoinedResponseFactory $queueJoinedResponseFactory,
        private GenericFailureResponseFactory $genericFailureResponseFactory,
    ) {
    }

    public function supports(SlackCommand $command): bool
    {
        return $command->getCommand() === Command::BBQ && $command->getSubCommand() === SubCommand::JOIN;
    }

    public function handle(SlackCommand $command): void
    {
        try {
            $queue = $this->queueManager->joinQueue(
                $command->getArgument('queue'),
                $command->getDomain(),
                $command->getUserId()
            );

            $response = $this->queueJoinedResponseFactory->create($queue, $command->getUserId());
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getDomain(),
                $exception->getUserId()
            );
        } catch (UnableToJoinQueueException $exception) {
            $response = $this->unableToJoinQueueResponseFactory->create($exception->getQueue());
        } finally {
            $response ??= $this->genericFailureResponseFactory->create();

            $command->setResponse($response);
        }
    }
}
