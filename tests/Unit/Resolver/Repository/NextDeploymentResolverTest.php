<?php

declare(strict_types=1);

namespace App\Tests\Unit\Resolver\Repository;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Filter\Deployment\DeploymentFilterInterface;
use App\Resolver\Repository\NextDeploymentResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(NextDeploymentResolver::class)]
class NextDeploymentResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnNullIfRepositoryBlockedByDeployment(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('isBlockedByDeployment')
            ->willReturn(true);

        $resolver = new NextDeploymentResolver([]);

        $result = $resolver->resolve($repository);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfRepositoryHasNoDeployments(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('isBlockedByDeployment')
            ->willReturn(false);

        $repository->expects($this->once())
            ->method('getSortedDeploymentsIncludingBlockedRepositories')
            ->willReturn([]);

        $resolver = new NextDeploymentResolver([]);

        $result = $resolver->resolve($repository);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldFilterByFiltersAndReturnNullIfResultEmpty(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('isBlockedByDeployment')
            ->willReturn(false);

        $repository->expects($this->once())
            ->method('getSortedDeploymentsIncludingBlockedRepositories')
            ->willReturn($deployments = [$this->createStub(Deployment::class)]);

        $filter = $this->createMock(DeploymentFilterInterface::class);
        $filter->expects($this->once())
            ->method('filter')
            ->with($deployments)
            ->willReturn([]);

        $resolver = new NextDeploymentResolver([$filter]);

        $result = $resolver->resolve($repository);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnFirstValueFromFilteredDeployments(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('isBlockedByDeployment')
            ->willReturn(false);

        $repository->expects($this->once())
            ->method('getSortedDeploymentsIncludingBlockedRepositories')
            ->willReturn($deployments = [
                $this->createStub(Deployment::class),
                $firstDeployment = $this->createStub(Deployment::class),
                $deployment = $this->createStub(Deployment::class),
            ]);

        $filter = $this->createMock(DeploymentFilterInterface::class);
        $filter->expects($this->once())
            ->method('filter')
            ->with($deployments)
            ->willReturn([$firstDeployment, $deployment]);

        $resolver = new NextDeploymentResolver([$filter]);

        $result = $resolver->resolve($repository);

        $this->assertSame($firstDeployment, $result);
    }
}
