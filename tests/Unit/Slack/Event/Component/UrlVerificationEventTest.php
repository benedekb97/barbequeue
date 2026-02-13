<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Event\Component;

use App\Slack\Event\Component\UrlVerificationEvent;
use App\Slack\Event\Event;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UrlVerificationEvent::class)]
class UrlVerificationEventTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $event = new UrlVerificationEvent(
            $token = 'token',
            $challenge = 'challenge',
        );

        $this->assertEquals(Event::URL_VERIFICATION, $event->getType());
        $this->assertSame($token, $event->getToken());
        $this->assertSame($challenge, $event->getChallenge());
    }
}
