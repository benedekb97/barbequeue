<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Event\Factory;

use App\Slack\Event\Component\AppHomeOpenedEvent;
use App\Slack\Event\Event;
use App\Slack\Event\Factory\AppHomeOpenedSlackEventFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(AppHomeOpenedSlackEventFactory::class)]
class AppHomeOpenedSlackEventFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldSupportAppHomeOpenedEvent(): void
    {
        $factory = new AppHomeOpenedSlackEventFactory();

        $this->assertTrue($factory->supports(Event::APP_HOME_OPENED));
    }

    #[Test]
    public function itShouldNotSupportOtherEvent(): void
    {
        $factory = new AppHomeOpenedSlackEventFactory();

        $this->assertFalse($factory->supports(Event::URL_VERIFICATION));
    }

    #[Test, DataProvider('provideFirstTime')]
    public function itShouldCreateAppHomeOpenedEvent(bool $firstTime): void
    {
        $request = new Request(request: [
            'team_id' => $teamId = 'teamId',
            'event' => array_filter([
                'user' => $userId = 'userId',
                'channel' => $channel = 'channel',
                'tab' => $tab = 'tab',
                'view' => $firstTime ? null : 'view',
            ]),
        ]);

        $factory = new AppHomeOpenedSlackEventFactory();

        $result = $factory->create($request);

        $this->assertInstanceOf(AppHomeOpenedEvent::class, $result);

        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($teamId, $result->getTeamId());
        $this->assertSame($channel, $result->getChannel());
        $this->assertSame($tab, $result->getTab());
        $this->assertEquals($firstTime, $result->isFirstTime());
    }

    public static function provideFirstTime(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
