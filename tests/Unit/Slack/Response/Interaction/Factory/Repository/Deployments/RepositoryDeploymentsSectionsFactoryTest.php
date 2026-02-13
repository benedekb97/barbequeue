<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository\Deployments;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Repository\Deployments\Block\RepositoryDeploymentSectionFactory;
use App\Slack\Response\Interaction\Factory\Repository\Deployments\RepositoryDeploymentsSectionsFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryDeploymentsSectionsFactory::class)]
class RepositoryDeploymentsSectionsFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSingleSectionIfRepositoryIsEmpty(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getDeployments')
            ->willReturn($collection = $this->createMock(Collection::class));

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $factory = new RepositoryDeploymentsSectionsFactory(
            $this->createStub(RepositoryDeploymentSectionFactory::class),
        );

        $result = $factory->create($repository);

        $this->assertCount(1, $result);

        $this->assertSectionBlockCorrectlyFormatted(
            '`repositoryName` is not being deployed to.',
            $result[0]->toArray(),
        );
    }

    #[Test]
    public function itShouldCreateSectionBlockForEveryDeployment(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getDeployments')
            ->willReturn($collection = $this->createMock(Collection::class));

        $collection->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $repository->expects($this->once())
            ->method('getSortedDeployments')
            ->willReturn([
                $deployment = $this->createStub(Deployment::class),
            ]);

        $deploymentSectionFactory = $this->createMock(RepositoryDeploymentSectionFactory::class);
        $deploymentSectionFactory->expects($this->once())
            ->method('create')
            ->with($deployment, 1)
            ->willReturn($section = $this->createStub(SectionBlock::class));

        $factory = new RepositoryDeploymentsSectionsFactory($deploymentSectionFactory);

        $result = $factory->create($repository);

        $this->assertCount(2, $result);

        $this->assertSectionBlockCorrectlyFormatted(
            'Users deploying to `repositoryName`:',
            $result[0]->toArray(),
        );

        $this->assertSame($section, $result[1]);
    }
}
