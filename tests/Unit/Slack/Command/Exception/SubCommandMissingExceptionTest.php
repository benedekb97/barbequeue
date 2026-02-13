<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Command\Exception;

use App\Slack\Command\Command;
use App\Slack\Command\Exception\SubCommandMissingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SubCommandMissingException::class)]
class SubCommandMissingExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedProperties(): void
    {
        $command = Command::BBQ;

        $exception = new SubCommandMissingException($command);

        $this->assertEquals('Sub-command missing from command '.$command->value, $exception->getMessage());
        $this->assertSame($command, $exception->getCommand());
    }
}
