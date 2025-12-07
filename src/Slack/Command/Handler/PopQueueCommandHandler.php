<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Service\Queue\Exception\QueueNotFoundException;
use App\Service\Queue\QueueManager;
use App\Slack\Command\Command;
use App\Slack\Command\Component\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\QueuePoppedResponseFactory;
use App\Slack\Response\Interaction\Factory\UnrecognisedQueueResponseFactory;

readonly class PopQueueCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private QueueManager $queueManager,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private QueuePoppedResponseFactory $queuePoppedResponseFactory,
    ) {}

    public function supports(SlackCommand $command): bool
    {
        return $command->getCommand() === Command::BBQ_ADMIN && $command->getSubCommand() === SubCommand::POP_QUEUE;
    }

    public function handle(SlackCommand $command): void
    {
        try {
            $queue = $this->queueManager->popQueue($command->getArgument('queue'), $command->getDomain());

            $response = $this->queuePoppedResponseFactory->create($queue);
        } catch (QueueNotFoundException $exception) {
            $response = $this->unrecognisedQueueResponseFactory->create(
                $exception->getQueueName(),
                $exception->getDomain(),
                null,
                false
            );
        } finally {
            $response ??= null;

            $command->setResponse($response);
        }
    }
}
