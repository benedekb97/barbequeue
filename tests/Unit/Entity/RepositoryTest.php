<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Entity\Workspace;
use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Repository::class)]
class RepositoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedValues(): void
    {
        $repository = new Repository()
            ->setName($name = 'name')
            ->setUrl($url = 'url')
            ->setWorkspace($workspace = $this->createStub(Workspace::class));

        $this->assertSame($name, $repository->getName());
        $this->assertSame($url, $repository->getUrl());
        $this->assertSame($workspace, $repository->getWorkspace());

        $blocksRepository = $this->createMock(Repository::class);
        $blocksRepository->expects($this->once())
            ->method('addBlockedByDeployment')
            ->with($repository);

        $blocksRepository->expects($this->once())
            ->method('removeBlockedByDeployment')
            ->with($repository);

        $repository->addDeploymentBlocksRepository($blocksRepository);

        $this->assertCount(1, $repository->getDeploymentBlocksRepositories());
        $this->assertSame($blocksRepository, $repository->getDeploymentBlocksRepositories()->first());

        $repository->clearDeploymentBlocksRepositories();

        $this->assertCount(0, $repository->getDeploymentBlocksRepositories());

        $blockedBy = $this->createMock(Repository::class);
        $blockedBy->expects($this->once())
            ->method('addDeploymentBlocksRepository')
            ->with($repository);

        $blockedBy->expects($this->once())
            ->method('removeDeploymentBlocksRepository')
            ->with($repository);

        $repository->addBlockedByDeployment($blockedBy);

        $this->assertCount(1, $repository->getBlockedByDeployment());
        $this->assertSame($blockedBy, $repository->getBlockedByDeployment()->first());

        $repository->removeBlockedByDeployment($blockedBy);

        $this->assertCount(0, $repository->getBlockedByDeployment());
        $this->assertFalse($repository->getBlockedByDeployment()->first());

        $this->assertCount(0, $repository->getDeploymentQueues());

        $callCount = 0;

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->exactly(2))
            ->method('setRepository')
            ->willReturnCallback(function ($argument) use ($repository, &$callCount, $deployment) {
                if (1 === ++$callCount) {
                    $this->assertSame($repository, $argument);
                } else {
                    $this->assertNull($argument);
                }

                return $deployment;
            });

        $repository->addDeployment($deployment);

        $this->assertCount(1, $repository->getDeployments());
        $this->assertSame($deployment, $repository->getDeployments()->first());

        $repository->removeDeployment($deployment);

        $this->assertCount(0, $repository->getDeployments());
        $this->assertFalse($repository->getDeployments()->first());
    }

    #[Test]
    public function itShouldReturnActiveDeployment(): void
    {
        $repository = new Repository();

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('setRepository')
            ->with($repository);

        $deployment->expects($this->once())
            ->method('isActive')
            ->willReturn(true);

        $repository->addDeployment($deployment);

        $this->assertSame($deployment, $repository->getActiveDeployment());
    }

    #[Test]
    public function itShouldReturnNullIfNoActiveDeployment(): void
    {
        $repository = new Repository();

        $this->assertNull($repository->getActiveDeployment());
    }

    #[Test]
    public function itShouldReturnActiveDeploymentOnGetBlockingDeployment(): void
    {
        $repository = new Repository();

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('setRepository')
            ->with($repository);

        $deployment->expects($this->exactly(2))
            ->method('isActive')
            ->willReturn(true);

        $repository->addDeployment($deployment);

        $this->assertSame($deployment, $repository->getBlockingDeployment());
        $this->assertTrue($repository->isBlockedByDeployment());
    }

    #[Test]
    public function itShouldReturnActiveDeploymentOnBlockerOnGetBlockingDeployment(): void
    {
        $repository = new Repository();

        $blockingRepository = $this->createMock(Repository::class);
        $blockingRepository->expects($this->once())
            ->method('addDeploymentBlocksRepository')
            ->with($repository);

        $repository->addBlockedByDeployment($blockingRepository);

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->exactly(2))
            ->method('isActive')
            ->willReturn(true);

        $blockingRepository->expects($this->exactly(2))
            ->method('getDeployments')
            ->willReturn(new ArrayCollection([$deployment]));

        $this->assertSame($deployment, $repository->getBlockingDeployment());
        $this->assertTrue($repository->isBlockedByDeployment());
    }

    #[Test]
    public function itShouldReturnNullOnGetBlockingDeploymentIfNoneExist(): void
    {
        $repository = new Repository();

        $blockingRepository = $this->createMock(Repository::class);
        $blockingRepository->expects($this->once())
            ->method('addDeploymentBlocksRepository')
            ->with($repository);

        $repository->addBlockedByDeployment($blockingRepository);

        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->exactly(2))
            ->method('isActive')
            ->willReturn(false);

        $blockingRepository->expects($this->exactly(2))
            ->method('getDeployments')
            ->willReturn(new ArrayCollection([$deployment]));

        $this->assertNull($repository->getBlockingDeployment());
        $this->assertFalse($repository->isBlockedByDeployment());
    }

    #[Test]
    public function itShouldReturnEmptyArrayIfNoDeployments(): void
    {
        $repository = new Repository();

        $this->assertEmpty($repository->getSortedDeployments());
    }

    #[Test]
    public function itShouldSortDeploymentsByCreatedAt(): void
    {
        $firstDeployment = $this->createMock(Deployment::class);
        $firstDeployment->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::now()->subHour());

        $secondDeployment = $this->createMock(Deployment::class);
        $secondDeployment->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::now()->addHour());

        $repository = new Repository()
            ->addDeployment($secondDeployment)
            ->addDeployment($firstDeployment);

        $sortedDeployments = $repository->getSortedDeployments();

        $this->assertCount(2, $sortedDeployments);
        $this->assertSame($firstDeployment, $sortedDeployments[0]);
        $this->assertSame($secondDeployment, $sortedDeployments[1]);
    }

    #[Test]
    public function itShouldGetSortedDeploymentsFromBlockedRepositories(): void
    {
        $repository = new Repository();

        $blockedRepository = $this->createMock(Repository::class);
        $blockedRepository->expects($this->once())
            ->method('getDeployments')
            ->willReturn(new ArrayCollection([
                $secondDeployment = $this->createMock(Deployment::class),
                $firstDeployment = $this->createMock(Deployment::class),
            ]));

        $blockedRepository->expects($this->once())
            ->method('addBlockedByDeployment')
            ->with($repository);

        $repository->addDeploymentBlocksRepository($blockedRepository);

        $firstDeployment->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::now()->subHour());

        $secondDeployment->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::now()->addHour());

        $result = $repository->getSortedDeploymentsIncludingBlockedRepositories();

        $this->assertCount(2, $result);

        $this->assertSame($firstDeployment, $result[0]);
        $this->assertSame($secondDeployment, $result[1]);
    }

    #[Test]
    public function itShouldSkipRepositoriesWithActiveDeploymentsOnGetSortedDeploymentsFromBlockedRepositories(): void
    {
        $repository = new Repository();

        $blockedRepository = $this->createMock(Repository::class);
        $blockedRepository->expects($this->once())
            ->method('getDeployments')
            ->willReturn(new ArrayCollection([
                $secondDeployment = $this->createMock(Deployment::class),
                $firstDeployment = $this->createMock(Deployment::class),
            ]));

        $blockedRepository->expects($this->once())
            ->method('getActiveDeployment')
            ->willReturn(null);

        $blockedRepository->expects($this->once())
            ->method('addBlockedByDeployment')
            ->with($repository);

        $blockedRepositoryWithActiveDeployment = $this->createMock(Repository::class);
        $blockedRepositoryWithActiveDeployment->expects($this->once())
            ->method('getActiveDeployment')
            ->willReturN($this->createStub(Deployment::class));

        $blockedRepositoryWithActiveDeployment->expects($this->once())
            ->method('addBlockedByDeployment')
            ->with($repository);

        $repository->addDeploymentBlocksRepository($blockedRepository);
        $repository->addDeploymentBlocksRepository($blockedRepositoryWithActiveDeployment);

        $firstDeployment->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::now()->subHour());

        $secondDeployment->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn(CarbonImmutable::now()->addHour());

        $result = $repository->getSortedDeploymentsIncludingBlockedRepositories();

        $this->assertCount(2, $result);

        $this->assertSame($firstDeployment, $result[0]);
        $this->assertSame($secondDeployment, $result[1]);
    }
}
