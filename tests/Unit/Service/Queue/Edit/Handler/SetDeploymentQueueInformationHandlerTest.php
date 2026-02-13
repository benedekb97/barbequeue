<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Edit\Handler;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Enum\QueueBehaviour;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Edit\EditQueueContext;
use App\Service\Queue\Edit\Handler\SetDeploymentQueueInformationHandler;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SetDeploymentQueueInformationHandler::class)]
class SetDeploymentQueueInformationHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextWithDeploymentQueue(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(DeploymentQueue::class));

        $handler = new SetDeploymentQueueInformationHandler($this->getLogger());

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportContextWithOtherQueueType(): void
    {
        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->createStub(Queue::class));

        $handler = new SetDeploymentQueueInformationHandler($this->getLogger());

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfContextNotEditQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new SetDeploymentQueueInformationHandler($this->getLogger());

        $handler->handle($context);
    }

    #[Test]
    public function itShouldSetDeploymentInformationOnQueue(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn($queueId = 1);

        $queue->expects($this->once())
            ->method('setBehaviour')
            ->with($behaviour = QueueBehaviour::ALLOW_SIMULTANEOUS)
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('clearRepositories')
            ->willReturnSelf();

        $queue->expects($this->once())
            ->method('addRepository')
            ->with($repository = $this->createStub(Repository::class));

        $context = $this->createMock(EditQueueContext::class);
        $context->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $context->expects($this->once())
            ->method('getBehaviour')
            ->willReturn($behaviour);

        $context->expects($this->once())
            ->method('getRepositories')
            ->willReturn(new ArrayCollection([$repository]));

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn(ContextType::EDIT);

        $this->expectsDebug(
            'Setting deployment queue information for {queue} on {contextId} {contextType}',
            [
                'queue' => $queueId,
                'contextId' => $contextId,
                'contextType' => ContextType::EDIT->value,
            ]
        );

        $handler = new SetDeploymentQueueInformationHandler($this->getLogger());

        $handler->handle($context);
    }
}
