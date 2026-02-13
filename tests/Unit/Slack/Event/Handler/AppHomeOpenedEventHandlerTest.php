<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Event\Handler;

use App\Slack\Event\Component\AppHomeOpenedEvent;
use App\Slack\Event\Component\SlackEventInterface;
use App\Slack\Event\Handler\AppHomeOpenedEventHandler;
use App\Slack\Surface\Component\HomeSurface;
use App\Slack\Surface\Factory\Exception\WorkspaceNotFoundException;
use App\Slack\Surface\Factory\Home\HomeViewFactory;
use App\Slack\Surface\Service\HomeService;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;

#[CoversClass(AppHomeOpenedEventHandler::class)]
class AppHomeOpenedEventHandlerTest extends LoggerAwareTestCase
{
    #[Test]
    public function itShouldNotSupportGenericSlackEvent(): void
    {
        $event = $this->createStub(SlackEventInterface::class);

        $handler = new AppHomeOpenedEventHandler(
            $this->createStub(LoggerInterface::class),
            $this->createStub(HomeViewFactory::class),
            $this->createStub(HomeService::class),
        );

        $this->assertFalse($handler->supports($event));
    }

    #[Test]
    public function itShouldSupportAppHomeOpenedEvent(): void
    {
        $event = $this->createStub(AppHomeOpenedEvent::class);

        $handler = new AppHomeOpenedEventHandler(
            $this->createStub(LoggerInterface::class),
            $this->createStub(HomeViewFactory::class),
            $this->createStub(HomeService::class),
        );

        $this->assertTrue($handler->supports($event));
    }

    #[Test]
    public function itShouldReturnIfEventNotAppHomeOpenedEvent(): void
    {
        $event = $this->createStub(SlackEventInterface::class);

        $this->expectNotToPerformAssertions();

        $handler = new AppHomeOpenedEventHandler(
            $this->createStub(LoggerInterface::class),
            $this->createStub(HomeViewFactory::class),
            $this->createStub(HomeService::class),
        );

        $handler->handle($event);
    }

    #[Test]
    public function itShouldReturnIfEventTabIsNotHome(): void
    {
        $event = $this->createMock(AppHomeOpenedEvent::class);
        $event->expects($this->once())
            ->method('getTab')
            ->willReturn('notHome');

        $handler = new AppHomeOpenedEventHandler(
            $this->createStub(LoggerInterface::class),
            $this->createStub(HomeViewFactory::class),
            $this->createStub(HomeService::class),
        );

        $handler->handle($event);
    }

    #[Test]
    public function itShouldLogAlertIfWorkspaceNotFoundExceptionThrown(): void
    {
        $this->expectsAlert('Received event for unknown workspace {workspaceId}', [
            'workspaceId' => $workspaceId = 'workspaceId',
        ]);

        $event = $this->createMock(AppHomeOpenedEvent::class);
        $event->expects($this->once())
            ->method('getTab')
            ->willReturn('home');

        $event->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $event->expects($this->once())
            ->method('getTeamId')
            ->willReturn($workspaceId);

        $event->expects($this->once())
            ->method('isFirstTime')
            ->willReturn($firstTime = true);

        $exception = $this->createMock(WorkspaceNotFoundException::class);
        $exception->expects($this->once())
            ->method('getWorkspaceId')
            ->willReturn($workspaceId);

        $factory = $this->createMock(HomeViewFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($userId, $workspaceId, $firstTime)
            ->willThrowException($exception);

        $handler = new AppHomeOpenedEventHandler(
            $this->getLogger(),
            $factory,
            $this->createStub(HomeService::class),
        );

        $handler->handle($event);
    }

    #[Test]
    public function itShouldPublishHomeView(): void
    {
        $event = $this->createMock(AppHomeOpenedEvent::class);
        $event->expects($this->once())
            ->method('getTab')
            ->willReturn('home');

        $event->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $event->expects($this->once())
            ->method('getTeamId')
            ->willReturn($workspaceId = 'workspaceId');

        $event->expects($this->once())
            ->method('isFirstTime')
            ->willReturn($firstTime = true);

        $factory = $this->createMock(HomeViewFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($userId, $workspaceId, $firstTime)
            ->willReturn($home = $this->createStub(HomeSurface::class));

        $service = $this->createMock(HomeService::class);
        $service->expects($this->once())
            ->method('publish')
            ->with($home);

        $handler = new AppHomeOpenedEventHandler(
            $this->getLogger(),
            $factory,
            $service,
        );

        $handler->handle($event);
    }
}
