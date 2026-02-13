<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\Repository;

use App\Entity\Repository;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\Repository\BlocksRepositoriesDefaultValueResolver;
use App\Tests\Unit\Slack\Surface\Factory\Resolver\WithOptionAssertions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(BlocksRepositoriesDefaultValueResolver::class)]
class BlocksRepositoriesDefaultValueResolverTest extends KernelTestCase
{
    use WithOptionAssertions;

    #[Test]
    public function itShouldSupportArgumentQueueRepositories(): void
    {
        $resolver = new BlocksRepositoriesDefaultValueResolver();

        $this->assertEquals(ModalArgument::REPOSITORY_BLOCKS, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveStringNull(): void
    {
        $resolver = new BlocksRepositoriesDefaultValueResolver();

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldResolveEmptyArrayIfRepositoryNotSet(): void
    {
        $resolver = new BlocksRepositoriesDefaultValueResolver();

        $this->assertEmpty($resolver->resolveArray());
    }

    #[Test]
    public function itShouldMapRepositoriesCorrectlyToOptions(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getDeploymentBlocksRepositories')
            ->willReturn(new ArrayCollection([$blockedRepository = $this->createMock(Repository::class)]));

        $blockedRepository->expects($this->once())
            ->method('getName')
            ->willReturn($repositoryName = 'repositoryName');

        $blockedRepository->expects($this->once())
            ->method('getId')
            ->willReturn($repositoryId = 1);

        $resolver = new BlocksRepositoriesDefaultValueResolver()
            ->setRepository($repository);

        $result = $resolver->resolveArray();

        $this->assertisArray($result);
        $this->assertCount(1, $result);
        $this->assertOptionFormedCorrectly($result[0], (string) $repositoryId, $repositoryName);
    }
}
