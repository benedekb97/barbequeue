<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Repository\QueueRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\Component\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\ListQueuedUsersResponseFactory;
use App\Slack\Response\Interaction\Factory\QueueEmptyResponseFactory;
use App\Slack\Response\Interaction\Factory\UnrecognisedQueueResponseFactory;

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
        return $command->getCommand() === Command::BBQ && $command->getSubCommand() === SubCommand::LIST;
    }

    public function handle(SlackCommand $command): void
    {
        $queue = $this->queueRepository->findOneByNameAndDomain(
            $queueName = $command->getArgument('queue'),
            $domain = $command->getDomain()
        );

        if ($queue === null) {
            $command->setResponse(
                $this->unrecognisedQueueResponseFactory->create($queueName, $domain, $command->getUserId())
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
