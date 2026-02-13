<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Exception;

use App\Entity\Queue;
use App\Service\Queue\Exception\InvalidDeploymentUrlException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InvalidDeploymentUrlException::class)]
class InvalidDeploymentUrlExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $exception = new InvalidDeploymentUrlException(
            $link = 'link',
            $queue = $this->createStub(Queue::class),
        );

        $this->assertSame($link, $exception->getDeploymentLink());
        $this->assertSame($queue, $exception->getQueue());
    }
}
