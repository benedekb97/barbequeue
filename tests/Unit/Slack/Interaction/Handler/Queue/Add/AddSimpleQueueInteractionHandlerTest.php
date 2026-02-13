<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Add;

use App\Entity\Administrator;
use App\Entity\Queue;
use App\Entity\Workspace;
use App\Event\HomeTabUpdatedEvent;
use App\Factory\QueueFactory;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Queue\Add\AddSimpleQueueInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Add\QueueAddedResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;

#[CoversClass(AddSimpleQueueInteractionHandler::class)]
class AddSimpleQueueInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldReturnIfNotSlackViewSubmission(): void
    {
        $this->expectNotToPerformAssertions();

        /** @var AddSimpleQueueInteractionHandler $handler */
        $handler = $this->getHandler();

        $handler->run($this->createStub(SlackInteraction::class));
    }

    #[Test]
    public function itShouldCreateDeploymentQueue(): void
    {
        $queueName = 'queueName';
        $maxEntries = 2;
        $expiryMinutes = 30;

        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->once())
            ->method('getArgumentString')
            ->with('queue_name')
            ->willReturn($queueName);

        $interaction->expects($this->once())
            ->method('getAdministrator')
            ->willReturn($administrator = $this->createMock(Administrator::class));

        $argumentIntegerCallCount = 0;
        $interaction->expects($this->exactly(2))
            ->method('getArgumentInteger')
            ->willReturnCallback(function ($argument) use (&$argumentIntegerCallCount, $maxEntries, $expiryMinutes) {
                if (1 === ++$argumentIntegerCallCount) {
                    $this->assertEquals('maximum_entries_per_user', $argument);

                    return $maxEntries;
                }

                $this->assertEquals('expiry_minutes', $argument);

                return $expiryMinutes;
            });

        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $interaction->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $interaction->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $interaction->expects($this->once())
            ->method('setResponse')
            ->with($response = $this->createStub(SlackInteractionResponse::class));

        $factory = $this->createMock(QueueFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with(
                $queueName,
                $workspace,
                $maxEntries,
                $expiryMinutes,
            )
            ->willReturn($queue = $this->createStub(Queue::class));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($queue);

        $entityManager->expects($this->once())
            ->method('flush');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($userId, $teamId) {
                $this->assertInstanceOf(HomeTabUpdatedEvent::class, $event);
                $this->assertSame($userId, $event->getUserId());
                $this->assertSame($teamId, $event->getTeamId());
            });

        $responseFactory = $this->createMock(QueueAddedResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($response);

        $handler = new AddSimpleQueueInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $factory,
            $entityManager,
            $eventDispatcher,
            $responseFactory,
        );

        $handler->run($interaction);
    }

    protected function getSupportedInteraction(): Interaction
    {
        return Interaction::ADD_SIMPLE_QUEUE;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::ADD_DEPLOYMENT_QUEUE;
    }

    protected function getSupportedInteractionType(): InteractionType
    {
        return InteractionType::VIEW_SUBMISSION;
    }

    protected function getUnsupportedInteractionType(): InteractionType
    {
        return InteractionType::BLOCK_ACTIONS;
    }

    protected function getHandler(): SlackInteractionHandlerInterface
    {
        return new AddSimpleQueueInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(QueueFactory::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(QueueAddedResponseFactory::class),
        );
    }
}
