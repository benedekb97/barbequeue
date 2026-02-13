<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Event\Exception;

use App\Slack\Event\Event;
use App\Slack\Event\Exception\UnhandledEventException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnhandledEventException::class)]
class UnhandledEventExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $exception = new UnhandledEventException($event = Event::URL_VERIFICATION);

        $this->assertSame($event, $exception->getEvent());
    }
}
