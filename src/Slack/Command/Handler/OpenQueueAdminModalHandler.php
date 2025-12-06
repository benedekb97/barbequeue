<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Repository\QueueRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\Component\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\UnrecognisedQueueResponseFactory;
use App\Slack\Surface\Service\ModalService;

readonly class OpenQueueAdminModalHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private ModalService $modalService,
    ) {
    }

    public function supports(SlackCommand $command): bool
    {
        return $command->getCommand() === Command::BBQ_ADMIN && $command->getSubCommand() === SubCommand::QUEUE;
    }

    public function handle(SlackCommand $command): void
    {
        $queue = $this->queueRepository->findOneByNameAndDomain(
            $queueName = $command->getArgument('queue'),
            $domain = $command->getDomain()
        );

        if ($queue === null) {
            $command->setResponse(
                $this->unrecognisedQueueResponseFactory->create($queueName, $domain, withActions: false)
            );

            return;
        }

        $this->modalService->createQueueModal($queue, $command);
    }
}
