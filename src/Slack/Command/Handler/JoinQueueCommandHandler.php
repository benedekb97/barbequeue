<?php

declare(strict_types=1);

namespace App\Slack\Command\Handler;

use App\Entity\QueuedUser;
use App\Repository\QueueRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\Component\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Command\Factory\QueueJoinedResponseFactory;
use App\Slack\Response\Command\Factory\UnableToJoinQueueResponseFactory;
use App\Slack\Response\Command\Factory\UnrecognisedQueueResponseFactory;
use Doctrine\ORM\EntityManagerInterface;

readonly class JoinQueueCommandHandler implements SlackCommandHandlerInterface
{
    public function __construct(
        private QueueRepositoryInterface $queueRepository,
        private UnrecognisedQueueResponseFactory $unrecognisedQueueResponseFactory,
        private UnableToJoinQueueResponseFactory $unableToJoinQueueResponseFactory,
        private QueueJoinedResponseFactory $queueJoinedResponseFactory,
        private EntityManagerInterface $entityManager,
    ) {}

    public function supports(SlackCommand $command): bool
    {
        return $command->getCommand() === Command::BBQ && $command->getSubCommand() === SubCommand::JOIN;
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

        if (!$queue->canJoin($userId = $command->getUserId())) {
            $command->setResponse($this->unableToJoinQueueResponseFactory->create($queue));

            return;
        }

        $queuedUser = new QueuedUser()
            ->setUserId($userId);

        $queue->addQueuedUser($queuedUser);

        $this->entityManager->persist($queuedUser);
        $this->entityManager->flush();

        $command->setResponse(
            $this->queueJoinedResponseFactory->create($queue)
        );
    }
}
