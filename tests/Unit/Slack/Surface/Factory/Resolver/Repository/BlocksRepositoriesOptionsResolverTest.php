<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\Repository;

use App\Entity\Repository;
use App\Repository\RepositoryRepositoryInterface;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Exception\NoOptionsAvailableException;
use App\Slack\Surface\Factory\Resolver\Repository\BlocksRepositoriesOptionsResolver;
use App\Tests\Unit\Slack\Surface\Factory\Resolver\WithOptionAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(BlocksRepositoriesOptionsResolver::class)]
class BlocksRepositoriesOptionsResolverTest extends KernelTestCase
{
    use WithOptionAssertions;

    #[Test]
    public function itShouldSupportRepositoryBlockersArgument(): void
    {
        $resolver = new BlocksRepositoriesOptionsResolver($this->createStub(RepositoryRepositoryInterface::class));

        $this->assertEquals(ModalArgument::REPOSITORY_BLOCKS, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveEmptyArrayIfTeamIdNotSet(): void
    {
        $resolver = new BlocksRepositoriesOptionsResolver($this->createStub(RepositoryRepositoryInterface::class));

        $this->assertEmpty($resolver->resolve());
    }

    #[Test]
    public function itShouldThrowNoOptionsAvailableExceptionIfRepositoryFoundNoResults(): void
    {
        $repository = $this->createMock(RepositoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByTeamId')
            ->with($teamId = 'teamId')
            ->willReturn([]);

        $resolver = new BlocksRepositoriesOptionsResolver($repository)
            ->setTeamId($teamId);

        $this->expectException(NoOptionsAvailableException::class);

        $resolver->resolve();
    }

    #[Test]
    public function itShouldMapRepositoriesToOptions(): void
    {
        $repositoryRepository = $this->createMock(RepositoryRepositoryInterface::class);
        $repositoryRepository->expects($this->once())
            ->method('findByTeamId')
            ->with($teamId = 'teamId')
            ->willReturn([$repository = $this->createMock(Repository::class)]);

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn($repositoryName = 'repositoryName');

        $repository->expects($this->once())
            ->method('getId')
            ->willReturn($repositoryId = 1);

        $resolver = new BlocksRepositoriesOptionsResolver($repositoryRepository)
            ->setTeamId($teamId);

        $result = $resolver->resolve();

        $this->assertCount(1, $result);

        $this->assertOptionFormedCorrectly($result[0], (string) $repositoryId, $repositoryName);
    }
}
