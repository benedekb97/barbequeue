<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataFixtures\Queue;

use App\DataFixtures\Queue\QueueFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueFixtures::class)]
class QueueFixturesTest extends KernelTestCase
{
    #[Test]
    public function itShouldPersistFiveQueues(): void
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->exactly(11))
            ->method('persist')
            ->withAnyParameters();

        $objectManager->expects($this->once())
            ->method('flush');

        $fixture = new QueueFixtures();
        $fixture->load($objectManager);
    }
}
