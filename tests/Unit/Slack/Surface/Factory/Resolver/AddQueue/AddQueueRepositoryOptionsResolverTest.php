<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\AddQueue;

use App\Entity\Repository;
use App\Entity\Workspace;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\AddQueue\AddQueueRepositoryOptionsResolver;
use App\Tests\Unit\Slack\Surface\Factory\Resolver\WithOptionAssertions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AddQueueRepositoryOptionsResolver::class)]
class AddQueueRepositoryOptionsResolverTest extends KernelTestCase
{
    use WithOptionAssertions;

    #[Test]
    public function itShouldSupportArgumentQueueRepositories(): void
    {
        $resolver = new AddQueueRepositoryOptionsResolver();

        $this->assertEquals(ModalArgument::QUEUE_REPOSITORIES, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldReturnEmptyArrayIfWorkspaceIsNull(): void
    {
        $resolver = new AddQueueRepositoryOptionsResolver()->setWorkspace(null);

        $result = $resolver->resolve();

        $this->assertEmpty($result);
    }

    #[Test]
    public function itShouldMapRepositoriesToOptions(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getRepositories')
            ->willReturn(new ArrayCollection([$repository = $this->createMock(Repository::class)]));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $repository->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $result = new AddQueueRepositoryOptionsResolver()
            ->setWorkspace($workspace)->resolve();

        $this->assertCount(1, $result);
        $this->assertOptionFormedCorrectly($result[0], '1', 'repositoryName');
    }
}
