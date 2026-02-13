<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\Queue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Queue::class)]
class QueueTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnCorrectName(): void
    {
        $this->assertEquals('Simple', Queue::SIMPLE->getName());
        $this->assertEquals('Deployment', Queue::DEPLOYMENT->getName());
    }
}
