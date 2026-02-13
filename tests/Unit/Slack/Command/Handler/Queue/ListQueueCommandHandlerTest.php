<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Handler\Queue;

use App\Entity\Queue;
use App\Repository\QueueRepositoryInterface;
use App\Slack\Command\Command;
use App\Slack\Command\CommandArgument;
use App\Slack\Command\Handler\Queue\ListQueueCommandHandler;
use App\Slack\Command\Handler\SlackCommandHandlerInterface;
use App\Slack\Command\SlackCommand;
use App\Slack\Command\SubCommand;
use App\Slack\Response\Interaction\Factory\Queue\Common\QueueEmptyResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\ListQueuedUsersResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Tests\Unit\Slack\Command\Handler\AbstractCommandHandlerTestCase;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListQueueCommandHandler::class)]
class ListQueueCommandHandlerTest extends AbstractCommandHandlerTestCase
{
    #[Test]
    public function itShouldCreateUnrecognisedQueueResponseIfQueueNotFound(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $command->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($queueName, $teamId)
            ->willReturn(null);

        $response = $this->createStub(SlackInteractionResponse::class);

        $unrecognisedQueueResponseFactory = $this->createMock(UnrecognisedQueueResponseFactory::class);
        $unrecognisedQueueResponseFactory->expects($this->once())
            ->method('create')
            ->with($queueName, $teamId, $userId)
            ->willReturn($response);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new ListQueueCommandHandler(
            $repository,
            $unrecognisedQueueResponseFactory,
            $this->createStub(QueueEmptyResponseFactory::class),
            $this->createStub(ListQueuedUsersResponseFactory::class),
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateQueueEmptyResponseFactoryIfQueueEmpty(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('isEmpty')
            ->willReturn(true);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($queueName, $teamId)
            ->willReturn($queue);

        $response = $this->createStub(SlackInteractionResponse::class);

        $queueEmptyResponseFactory = $this->createMock(QueueEmptyResponseFactory::class);
        $queueEmptyResponseFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($response);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new ListQueueCommandHandler(
            $repository,
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $queueEmptyResponseFactory,
            $this->createStub(ListQueuedUsersResponseFactory::class),
        );

        $handler->handle($command);
    }

    #[Test]
    public function itShouldCreateListQueuedUsersResponseIfQueueNotEmpty(): void
    {
        $command = $this->createMock(SlackCommand::class);
        $command->expects($this->once())
            ->method('getArgumentString')
            ->with(CommandArgument::QUEUE)
            ->willReturn($queueName = 'queueName');

        $command->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('isEmpty')
            ->willReturn(false);

        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getQueuedUsers')
            ->willReturn($collection);

        $repository = $this->createMock(QueueRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByNameAndTeamId')
            ->with($queueName, $teamId)
            ->willReturn($queue);

        $response = $this->createStub(SlackInteractionResponse::class);

        $listQueuedUsersResponseFactory = $this->createMock(ListQueuedUsersResponseFactory::class);
        $listQueuedUsersResponseFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($response);

        $command->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $handler = new ListQueueCommandHandler(
            $repository,
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(QueueEmptyResponseFactory::class),
            $listQueuedUsersResponseFactory,
        );

        $handler->handle($command);
    }

    protected function getSupportedCommand(): Command
    {
        return Command::BBQ;
    }

    protected function getSupportedSubCommand(): SubCommand
    {
        return SubCommand::LIST;
    }

    protected function getUnsupportedCommand(): Command
    {
        return Command::BBQ_ADMIN;
    }

    protected function getUnsupportedSubCommand(): SubCommand
    {
        return SubCommand::JOIN;
    }

    protected function getHandler(): SlackCommandHandlerInterface
    {
        return new ListQueueCommandHandler(
            $this->createStub(QueueRepositoryInterface::class),
            $this->createStub(UnrecognisedQueueResponseFactory::class),
            $this->createStub(QueueEmptyResponseFactory::class),
            $this->createStub(ListQueuedUsersResponseFactory::class),
        );
    }
}
