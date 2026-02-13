<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Queue\Exception;

use App\Entity\Queue;
use App\Service\Queue\Exception\DeploymentInformationRequiredException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentInformationRequiredException::class)]
class DeploymentInformationRequiredExceptionTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedQueue(): void
    {
        $exception = new DeploymentInformationRequiredException(
            $queue = $this->createStub(Queue::class),
        );

        $this->assertSame($queue, $exception->getQueue());
    }
}
