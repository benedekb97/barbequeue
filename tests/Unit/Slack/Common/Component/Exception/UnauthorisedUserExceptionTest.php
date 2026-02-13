<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Common\Component\Exception;

use App\Slack\Command\Command;
use App\Slack\Common\Component\Exception\UnauthorisedUserException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnauthorisedUserException::class)]
class UnauthorisedUserExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedArguments(): void
    {
        $exception = new UnauthorisedUserException(
            $command = Command::BBQ_ADMIN,
        );

        $this->assertSame($command, $exception->getEnum());
    }
}
