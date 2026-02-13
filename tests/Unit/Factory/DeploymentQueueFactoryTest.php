<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Repository;
use App\Entity\Workspace;
use App\Enum\QueueBehaviour;
use App\Factory\DeploymentQueueFactory;
use App\Repository\RepositoryRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentQueueFactory::class)]
class DeploymentQueueFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnEarlyIfWorkspaceIsNull(): void
    {
        $repository = $this->createMock(RepositoryRepositoryInterface::class);
        $repository->expects($this->never())
            ->method('findByIdsAndWorkspace');

        $factory = new DeploymentQueueFactory($repository);

        $result = $factory->create(
            $name = 'name',
            $workspace = null,
            $maxEntries = 1,
            $expiryMinutes = 2,
            [],
            'undefined',
        );

        $this->assertSame($name, $result->getName());
        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertSame($maxEntries, $result->getMaximumEntriesPerUser());
        $this->assertSame($expiryMinutes, $result->getExpiryMinutes());
        $this->assertEquals(QueueBehaviour::ENFORCE_QUEUE, $result->getBehaviour());
    }

    #[Test]
    public function itShouldAddRepositoriesToQueueIfFound(): void
    {
        $repositoryRepository = $this->createMock(RepositoryRepositoryInterface::class);
        $repositoryRepository->expects($this->once())
            ->method('findByIdsAndWorkspace')
            ->with(
                $repositoryIds = [1],
                $workspace = $this->createStub(Workspace::class),
            )
            ->willReturn([$repository = $this->createStub(Repository::class)]);

        $factory = new DeploymentQueueFactory($repositoryRepository);

        $result = $factory->create(
            $name = 'name',
            $workspace,
            $maxEntries = null,
            $expiryMinutes = null,
            $repositoryIds,
            'allow-jumps',
        );

        $this->assertSame($name, $result->getName());
        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertSame($maxEntries, $result->getMaximumEntriesPerUser());
        $this->assertSame($expiryMinutes, $result->getExpiryMinutes());
        $this->assertEquals(QueueBehaviour::ALLOW_JUMPS, $result->getBehaviour());

        $this->assertSame($repository, $result->getRepositories()->first());
    }
}
