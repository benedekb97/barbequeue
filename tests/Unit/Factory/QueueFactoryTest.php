<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Workspace;
use App\Factory\QueueFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueFactory::class)]
class QueueFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCreateQueue(): void
    {
        $factory = new QueueFactory();

        $result = $factory->create(
            $name = 'name',
            $workspace = $this->createStub(Workspace::class),
            $maxEntries = null,
            $expiry = 20,
        );

        $this->assertSame($name, $result->getName());
        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertSame($maxEntries, $result->getMaximumEntriesPerUser());
        $this->assertSame($expiry, $result->getExpiryMinutes());
    }
}
