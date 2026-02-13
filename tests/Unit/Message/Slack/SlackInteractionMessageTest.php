<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message\Slack;

use App\Message\Slack\SlackInteractionMessage;
use App\Slack\Interaction\SlackInteraction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackInteractionMessage::class)]
class SlackInteractionMessageTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedInteraction(): void
    {
        $interaction = $this->createStub(SlackInteraction::class);

        $message = new SlackInteractionMessage($interaction);

        $this->assertSame($interaction, $message->getInteraction());
    }
}
