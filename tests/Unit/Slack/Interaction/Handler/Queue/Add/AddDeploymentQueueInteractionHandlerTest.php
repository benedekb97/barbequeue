<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler\Queue\Add;

use App\Entity\Administrator;
use App\Entity\DeploymentQueue;
use App\Entity\Workspace;
use App\Event\HomeTabUpdatedEvent;
use App\Factory\DeploymentQueueFactory;
use App\Slack\Common\Validator\AuthorisationValidator;
use App\Slack\Interaction\Handler\Queue\Add\AddDeploymentQueueInteractionHandler;
use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Slack\Interaction\SlackViewSubmission;
use App\Slack\Response\Interaction\Factory\Administrator\UnauthorisedResponseFactory;
use App\Slack\Response\Interaction\Factory\Queue\Add\DeploymentQueueAddedResponseFactory;
use App\Slack\Response\Interaction\SlackInteractionResponse;
use App\Tests\Unit\Slack\Interaction\Handler\AbstractInteractionHandlerTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;

#[CoversClass(AddDeploymentQueueInteractionHandler::class)]
class AddDeploymentQueueInteractionHandlerTest extends AbstractInteractionHandlerTestCase
{
    #[Test]
    public function itShouldReturnIfNotSlackViewSubmission(): void
    {
        $this->expectNotToPerformAssertions();

        /** @var AddDeploymentQueueInteractionHandler $handler */
        $handler = $this->getHandler();

        $handler->run($this->createStub(SlackInteraction::class));
    }

    #[Test]
    public function itShouldCreateDeploymentQueue(): void
    {
        $queueName = 'queueName';
        $queueBehaviour = 'queueBehaviour';
        $maxEntries = 2;
        $expiryMinutes = 30;

        $argumentStringCallCount = 0;
        $interaction = $this->createMock(SlackViewSubmission::class);
        $interaction->expects($this->exactly(2))
            ->method('getArgumentString')
            ->willReturnCallback(function ($argument) use ($queueName, $queueBehaviour, &$argumentStringCallCount) {
                if (1 === ++$argumentStringCallCount) {
                    $this->assertEquals('queue_name', $argument);

                    return $queueName;
                }

                $this->assertEquals('queue_behaviour', $argument);

                return $queueBehaviour;
            });

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

        $interaction->expects($this->once())
            ->method('getArgumentIntArray')
            ->with('queue_repositories')
            ->willReturn($repositoryIds = [1]);

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

        $factory = $this->createMock(DeploymentQueueFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with(
                $queueName,
                $workspace,
                $maxEntries,
                $expiryMinutes,
                $repositoryIds,
                $queueBehaviour,
            )
            ->willReturn($queue = $this->createStub(DeploymentQueue::class));

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

        $responseFactory = $this->createMock(DeploymentQueueAddedResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($response);

        $handler = new AddDeploymentQueueInteractionHandler(
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
        return Interaction::ADD_DEPLOYMENT_QUEUE;
    }

    protected function getUnsupportedInteraction(): Interaction
    {
        return Interaction::ADD_SIMPLE_QUEUE;
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
        return new AddDeploymentQueueInteractionHandler(
            $this->createStub(AuthorisationValidator::class),
            $this->createStub(UnauthorisedResponseFactory::class),
            $this->createStub(DeploymentQueueFactory::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(DeploymentQueueAddedResponseFactory::class),
        );
    }
}
