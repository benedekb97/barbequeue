<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message\Slack;

use App\Message\Slack\SlackCommandMessage;
use App\Slack\Command\SlackCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackCommandMessage::class)]
class SlackCommandMessageTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedSlackCommand(): void
    {
        $command = $this->createStub(SlackCommand::class);

        $message = new SlackCommandMessage($command);

        $this->assertSame($command, $message->getCommand());
    }
}
