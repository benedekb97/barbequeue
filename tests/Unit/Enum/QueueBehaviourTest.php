<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\QueueBehaviour;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueBehaviour::class)]
class QueueBehaviourTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnCorrectName(): void
    {
        $this->assertEquals('Always enforce queue', QueueBehaviour::ENFORCE_QUEUE->getName());
        $this->assertEquals('Allow jumps', QueueBehaviour::ALLOW_JUMPS->getName());
        $this->assertEquals('Allow simultaneous', QueueBehaviour::ALLOW_SIMULTANEOUS->getName());
    }
}
