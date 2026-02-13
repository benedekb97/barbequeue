<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Handler;

use App\Entity\User;
use App\Entity\Workspace;
use App\Resolver\UserResolver;
use App\Service\Queue\Context\ContextType;
use App\Service\Queue\Context\QueueContextInterface;
use App\Service\Queue\Handler\ResolveUserHandler;
use App\Service\Queue\Join\JoinQueueContext;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ResolveUserHandler::class)]
class ResolveUserHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldSupportContextWithNoUser(): void
    {
        $handler = new ResolveUserHandler(
            $this->createStub(UserResolver::class),
            $this->getLogger(),
        );

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('hasUser')
            ->willReturn(false);

        $this->assertTrue($handler->supports($context));
    }

    #[Test]
    public function itShouldNotSupportContextWithUser(): void
    {
        $handler = new ResolveUserHandler(
            $this->createStub(UserResolver::class),
            $this->getLogger(),
        );

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('hasUser')
            ->willReturn(true);

        $this->assertFalse($handler->supports($context));
    }

    #[Test]
    public function itShouldSetUserOnContext(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getId')
            ->willReturn($workspaceId = 1);

        $context = $this->createMock(QueueContextInterface::class);
        $context->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $context->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->expects($this->once())
            ->method('resolve')
            ->with($userId, $workspace, null)
            ->willReturn($user = $this->createStub(User::class));

        $context->expects($this->once())
            ->method('setUser')
            ->with($user);

        $this->expectsDebug('Resolving user by {userId} and {workspaceId} for {contextId} {contextType}', [
            'userId' => $userId,
            'workspaceId' => $workspaceId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $handler = new ResolveUserHandler(
            $userResolver,
            $this->getLogger(),
        );

        $handler->handle($context);
    }

    #[Test]
    public function itShouldFetchUserNameIfJoinQueueContext(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getId')
            ->willReturn($workspaceId = 1);

        $context = $this->createMock(JoinQueueContext::class);
        $context->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $context->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $context->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName = 'userName');

        $context->expects($this->once())
            ->method('getId')
            ->willReturn($contextId = 'contextId');

        $context->expects($this->once())
            ->method('getType')
            ->willReturn($contextType = ContextType::LEAVE);

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->expects($this->once())
            ->method('resolve')
            ->with($userId, $workspace, $userName)
            ->willReturn($user = $this->createStub(User::class));

        $context->expects($this->once())
            ->method('setUser')
            ->with($user);

        $this->expectsDebug('Resolving user by {userId} and {workspaceId} for {contextId} {contextType}', [
            'userId' => $userId,
            'workspaceId' => $workspaceId,
            'contextId' => $contextId,
            'contextType' => $contextType->value,
        ]);

        $handler = new ResolveUserHandler(
            $userResolver,
            $this->getLogger(),
        );

        $handler->handle($context);
    }
}
