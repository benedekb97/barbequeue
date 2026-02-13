<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Event\Factory;

use App\Slack\Event\Component\UrlVerificationEvent;
use App\Slack\Event\Event;
use App\Slack\Event\Factory\UrlVerificationSlackEventFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(UrlVerificationSlackEventFactory::class)]
class UrlVerificationSlackEventFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldSupportUrlVerificationEvent(): void
    {
        $factory = new UrlVerificationSlackEventFactory();

        $this->assertTrue($factory->supports(Event::URL_VERIFICATION));
    }

    #[Test]
    public function itShouldNotSupportOtherEvent(): void
    {
        $factory = new UrlVerificationSlackEventFactory();

        $this->assertFalse($factory->supports(Event::APP_HOME_OPENED));
    }

    #[Test]
    public function itShouldCreateUrlVerificationEvent(): void
    {
        $factory = new UrlVerificationSlackEventFactory();

        $request = new Request(request: [
            'token' => $token = 'token',
            'challenge' => $challenge = 'challenge',
        ]);

        $result = $factory->create($request);

        $this->assertInstanceOf(UrlVerificationEvent::class, $result);
        $this->assertSame($token, $result->getToken());
        $this->assertSame($challenge, $result->getChallenge());
    }
}
