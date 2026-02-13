<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler\Queue;

use App\Repository\QueueRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\Queue\Common\QueueEmptyResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\ListQueuedUsersResponseFactory;

readonly class ListQueueCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private QueueEmptyResponseFactory $queueEmptyResponseFactory,
        private ListQueuedUsersResponseFactory $listQueuedUsersResponseFactory,
    ) {
    }

    public function supports(SlackCommand $command): bool
    {
        return Command::BBQ === $command->getCommand() && SubCommand::LIST === $command->getSubCommand();
    }

    public function handle(SlackCommand $command): void
    {
        $queue = $this->queueRepository->findOneByNameAndTeamId(
            $queueName = $command->getArgumentString(CommandArgument::QUEUE),
            $teamId = $command->getTeamId()
        );

        if (null === $queue) {
            $command->setResponse(
                $this->unrecognisedQueueResponseFactory->create($queueName, $teamId, $command->getUserId())
            );

            return;
        }

        if ($queue->getQueuedUsers()->isEmpty()) {
            $command->setResponse(
                $this->queueEmptyResponseFactory->create($queue)
            );

            return;
        }

        $command->setResponse(
            $this->listQueuedUsersResponseFactory->create($queue)
        );
    }
}
