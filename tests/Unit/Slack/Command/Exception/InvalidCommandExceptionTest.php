<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Exception;

use App\Slack\Command\Exception\InvalidCommandException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InvalidCommandException::class)]
class InvalidCommandExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedCommandText(): void
    {
        $exception = new InvalidCommandException($commandText = 'commandText');

        $this->assertSame($commandText, $exception->getCommandText());
    }
}
