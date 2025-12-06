<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\Exception\UnableToLeaveQueueException;
use App\Service\Queue\QueueManager;
use App\Slack\Command\Command;
use App\Slack\Command\Component\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\QueueLeftResponseFactory;
use App\Slack\Response\Interaction\Factory\UnableToLeaveQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\UnrecognisedQueueResponseFactory;

readonly class LeaveQueueCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private QueueManager $queueManager,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private UnableToLeaveQueueResponseFactory $unableToLeaveQueueResponseFactory,
        private QueueLeftResponseFactory $queueLeftResponseFactory,
    ) {}

    public function supports(SlackCommand $command): bool
    {
        return $command->getCommand() === Command::BBQ && $command->getSubCommand() === SubCommand::LEAVE;
    }

    public function handle(SlackCommand $command): void
    {
        try {
            $queue = $this->queueManager->leaveQueue(
                $command->getArgument('queue'),
                $command->getDomain(),
                $userId = $command->getUserId()
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
            $command->setResponse($response);
        }
    }
}
