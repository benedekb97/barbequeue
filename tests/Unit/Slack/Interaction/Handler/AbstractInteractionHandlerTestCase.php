<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Interaction\Handler;

use App\Slack\Interaction\Handler\SlackInteractionHandlerInterface;
use App\Slack\Interaction\Interaction;
use App\Slack\Interaction\InteractionType;
use App\Slack\Interaction\SlackInteraction;
use App\Tests\Unit\LoggerAwareTestCase;
use PHPUnit\Framework\Attributes\Test;

abstract class AbstractInteractionHandlerTestCase extends LoggerAwareTestCase
{
    abstract protected function getSupportedInteraction(): Interaction;

    abstract protected function getUnsupportedInteraction(): Interaction;

    abstract protected function getSupportedInteractionType(): InteractionType;

    abstract protected function getUnsupportedInteractionType(): InteractionType;

    abstract protected function getHandler(): SlackInteractionHandlerInterface;

    #[Test]
    public function itShouldSupportSupportedInteraction(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($this->getSupportedInteraction());

        $interaction->expects($this->once())
            ->method('getType')
            ->willReturn($this->getSupportedInteractionType());

        $this->assertTrue($this->getHandler()->supports($interaction));
    }

    #[Test]
    public function itShouldNotSupportUnsupportedInteraction(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($this->getUnsupportedInteraction());

        $interaction->expects($this->never())
            ->method('getType')
            ->willReturn($this->getSupportedInteractionType());

        $this->assertFalse($this->getHandler()->supports($interaction));
    }

    #[Test]
    public function itShouldNotSupportUnsupportedInteractionType(): void
    {
        $interaction = $this->createMock(SlackInteraction::class);
        $interaction->expects($this->once())
            ->method('getInteraction')
            ->willReturn($this->getSupportedInteraction());

        $interaction->expects($this->once())
            ->method('getType')
            ->willReturn($this->getUnsupportedInteractionType());

        $this->assertFalse($this->getHandler()->supports($interaction));
    }
}
