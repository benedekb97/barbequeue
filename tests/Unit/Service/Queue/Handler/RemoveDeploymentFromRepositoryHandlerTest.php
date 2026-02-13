<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Handler;

use App\Entity\Deployment;
use App\Entity\QueuedUser;
use App\Entity\Repository;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Handler\RemoveDeploymentFromRepositoryHandler;
use App\Service\Queue\Leave\LeaveQueueContext;
use App\Service\Queue\Pop\PopQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RemoveDeploymentFromRepositoryHandler::class)]
class RemoveDeploymentFromRepositoryHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportPopQueueContextWithDeployment(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $handler = new RemoveDeploymentFromRepositoryHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldSupportLeaveQueueContextWithDeployment(): void
    {
        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(Deployment::class));

        $handler = new RemoveDeploymentFromRepositoryHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportGenericContextWithDeployment(): void
    {
        $context = $this->createStub(QueueContextInterface::class);

        $handler = new RemoveDeploymentFromRepositoryHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportPopQueueContextWithQueuedUser(): void
    {
        $context = $this->createMock(PopQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(QueuedUser::class));

        $handler = new RemoveDeploymentFromRepositoryHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportLeaveQueueContextWithQueuedUser(): void
    {
        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($this->createStub(QueuedUser::class));

        $handler = new RemoveDeploymentFromRepositoryHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldReturnEarlyIfNotLeaveOrPopQueueContext(): void
    {
        $this->expectNotToPerformAssertions();

        $context = $this->createStub(QueueContextInterface::class);

        $handler = new RemoveDeploymentFromRepositoryHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldRemoveDeploymentFromRepository(): void
    {
        $repository = $this->createMock(Repository::class);

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $deployment->expects($this->once())
            ->method('getId')
            ->willReturn($deploymentId = 1);

        $repository->expects($this->once())
            ->method('removeDeployment')
            ->with($deployment)
            ->willReturnSelf();

        $repository->expects($this->once())
            ->method('getId')
            ->willReturn($repositoryId = 1);

        $context = $this->createMock(LeaveQueueContext::class);
        $context->expects($this->once())
            ->method('getQueuedUser')
            ->willReturn($deployment);

        $context->expects($this->once())
            ->method('setRepository')
            ->with($repository);

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $this->expectsDebug('Removing {deployment} from {repository} {contextId} {contextType}', [
            'deployment' => $deploymentId,
            'repository' => $repositoryId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($repository);

        $handler = new RemoveDeploymentFromRepositoryHandler(
            $entityManager,
            $this->getLogger(),
        );

        $handler->handle($context);
    }
}
