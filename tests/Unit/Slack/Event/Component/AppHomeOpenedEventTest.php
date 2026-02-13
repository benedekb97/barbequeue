<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Event\Component;

use App\Slack\Event\Component\AppHomeOpenedEvent;
use App\Slack\Event\Event;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AppHomeOpenedEvent::class)]
class AppHomeOpenedEventTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $event = new AppHomeOpenedEvent(
            $userId = 'userId',
            $teamId = 'teamId',
            $channel = 'channel',
            $tab = 'tab',
            $firstTime = false,
        );

        $this->assertEquals(Event::APP_HOME_OPENED, $event->getType());
        $this->assertSame($userId, $event->getUserId());
        $this->assertSame($teamId, $event->getTeamId());
        $this->assertSame($channel, $event->getChannel());
        $this->assertSame($tab, $event->getTab());
        $this->assertSame($firstTime, $event->isFirstTime());
    }
}
