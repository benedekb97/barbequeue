<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message\Slack;

use App\Message\Slack\SlackEventMessage;
use App\Slack\Event\Component\SlackEventInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackEventMessage::class)]
class SlackEventMessageTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameter(): void
    {
        $message = new SlackEventMessage(
            $event = $this->createStub(SlackEventInterface::class),
        );

        $this->assertSame($event, $message->getEvent());
    }
}
