<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Event\HomeTabUpdatedEvent;
use App\EventSubscriber\HomeTabEventSubscriber;
use App\Slack\Surface\Component\HomeSurface;
use App\Slack\Surface\Factory\Home\HomeViewFactory;
use App\Slack\Surface\Service\HomeService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(HomeTabEventSubscriber::class)]
class HomeTabEventSubscriberTest extends KernelTestCase
{
    #[Test]
    public function itShouldSubscribeToCorrectEvents(): void
    {
        $subscribedEvents = HomeTabEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(HomeTabUpdatedEvent::class, $subscribedEvents);
        $this->assertEquals(['handleUpdated'], $subscribedEvents[HomeTabUpdatedEvent::class]);
    }

    #[Test]
    public function itShouldPublishNewHomeView(): void
    {
        $event = $this->createMock(HomeTabUpdatedEvent::class);
        $event->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $event->expects($this->once())
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $homeViewFactory = $this->createMock(HomeViewFactory::class);
        $homeViewFactory->expects($this->once())
            ->method('create')
            ->with($userId, $teamId, false)
            ->willReturn($view = $this->createStub(HomeSurface::class));

        $homeService = $this->createMock(HomeService::class);
        $homeService->expects($this->once())
            ->method('publish')
            ->with($view);

        $subscriber = new HomeTabEventSubscriber($homeViewFactory, $homeService);

        $subscriber->handleUpdated($event);
    }
}
