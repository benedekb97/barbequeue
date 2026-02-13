<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Join\Handler;

use App\Entity\Deployment;
use App\Entity\QueuedUser;
use App\Entity\Repository;
use App\Event\Repository\RepositoryUpdatedEvent;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Join\Handler\DispatchRepositoryUpdatedEventHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;

#[CoversClass(DispatchRepositoryUpdatedEventHandler::class)]
class DispatchRepositoryUpdatedEventHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportJoinQueueContextWithDeployment(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportJoinQueueContextWithQueuedUser(): void
    {
        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(QueuedUser::class));

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportGenericQueueContext(): void
    {
        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->assertFalse($handler->supports($this->createStub(QueueContextInterface::class)));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotJoinQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->getLogger(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $handler->handle($this->createStub(QueueContextInterface::class));
    }

    #[Test]
    public function itShouldDispatchRepositoryUpdatedEvent(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getId')
            ->willReturn($repositoryId = 1);

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturn($repository);

        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($deployment);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($type = ContextType::JOIN);

        $this->expectsDebug('Dispatching repository updated event for {repository} {contextId} {contextType}', [
            'repository' => $repositoryId,
            'contextId' => $contextId,
            'contextType' => $type->value,
        ]);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($repository) {
                $this->assertInstanceOf(RepositoryUpdatedEvent::class, $event);
                $this->assertSame($repository, $event->getRepository());
            });

        $handler = new DispatchRepositoryUpdatedEventHandler(
            $this->getLogger(),
            $eventDispatcher,
        );

        $handler->handle($context);
    }
}
