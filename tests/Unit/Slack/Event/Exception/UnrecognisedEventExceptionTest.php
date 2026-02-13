<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Event\Exception;

use App\Slack\Event\Exception\UnrecognisedEventException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnrecognisedEventException::class)]
class UnrecognisedEventExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameter(): void
    {
        $exception = new UnrecognisedEventException(
            $type = 'type',
        );

        $this->assertSame($type, $exception->getType());
    }
}
