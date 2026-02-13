<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\Queue;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\Queue\RepositoriesDefaultValueResolver;
use App\Tests\Unit\Slack\Surface\Factory\Resolver\WithOptionAssertions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoriesDefaultValueResolver::class)]
class RepositoriesDefaultValueResolverTest extends KernelTestCase
{
    use WithOptionAssertions;

    #[Test]
    public function itShouldSupportArgumentQueueRepositories(): void
    {
        $resolver = new RepositoriesDefaultValueResolver();

        $this->assertEquals(ModalArgument::QUEUE_REPOSITORIES, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveStringNull(): void
    {
        $resolver = new RepositoriesDefaultValueResolver();

        $this->assertNull($resolver->resolveString());
    }

    #[Test]
    public function itShouldResolveEmptyArrayIfQueueNotSet(): void
    {
        $resolver = new RepositoriesDefaultValueResolver();

        $this->assertEmpty($resolver->resolveArray());
    }

    #[Test]
    public function itShouldResolveEmptyArrayIfQueueNotDeploymentQueue(): void
    {
        $resolver = new RepositoriesDefaultValueResolver()
            ->setQueue($this->createStub(Queue::class));

        $this->assertEmpty($resolver->resolveArray());
    }

    #[Test]
    public function itShouldMapRepositoriesOnQueueToOptions(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getRepositories')
            ->willReturn(new ArrayCollection([$repository = $this->createMock(Repository::class)]));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn($repositoryName = 'repositoryName');

        $repository->expects($this->once())
            ->method('getId')
            ->willReturn($repositoryId = 1);

        $resolver = new RepositoriesDefaultValueResolver()
            ->setQueue($queue);

        $result = $resolver->resolveArray();

        $this->assertisArray($result);
        $this->assertCount(1, $result);
        $this->assertOptionFormedCorrectly($result[0], (string) $repositoryId, $repositoryName);
    }
}
