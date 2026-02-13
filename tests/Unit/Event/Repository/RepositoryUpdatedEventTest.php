<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event\Repository;

use App\Entity\Repository;
use App\Event\Repository\RepositoryUpdatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryUpdatedEvent::class)]
class RepositoryUpdatedEventTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameter(): void
    {
        $event = new RepositoryUpdatedEvent(
            $repository = $this->createStub(Repository::class),
            $notificationsEnabled = true,
        );

        $this->assertSame($repository, $event->getRepository());
        $this->assertSame($notificationsEnabled, $event->areNotificationsEnabled());
    }
}
