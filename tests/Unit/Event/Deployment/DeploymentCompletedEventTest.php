<?php

declare(strict_types=1);

namespace App\Tests\Unit\Event\Deployment;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Event\Deployment\DeploymentCompletedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentCompletedEvent::class)]
class DeploymentCompletedEventTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedParameters(): void
    {
        $event = new DeploymentCompletedEvent(
            $deployment = $this->createStub(Deployment::class),
            $workspace = $this->createStub(Workspace::class),
            $repository = $this->createStub(Repository::class),
            $notifyOwner = false,
        );

        $this->assertSame($deployment, $event->getDeployment());
        $this->assertSame($workspace, $event->getWorkspace());
        $this->assertSame($repository, $event->getRepository());
        $this->assertSame($notifyOwner, $event->shouldNotifyOwner());
    }
}
