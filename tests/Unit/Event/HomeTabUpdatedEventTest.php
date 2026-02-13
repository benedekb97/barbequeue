<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event;

use App\Event\HomeTabUpdatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(HomeTabUpdatedEvent::class)]
class HomeTabUpdatedEventTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $event = new HomeTabUpdatedEvent($userId = 'userId', $teamId = 'teamId');

        $this->assertSame($userId, $event->getUserId());
        $this->assertSame($teamId, $event->getTeamId());
    }
}
