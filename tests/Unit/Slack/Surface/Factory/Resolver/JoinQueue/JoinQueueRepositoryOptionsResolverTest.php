<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Resolver\JoinQueue;

use App\Entity\DeploymentQueue;
use App\Entity\Repository;
use App\Slack\Surface\Component\ModalArgument;
use App\Slack\Surface\Factory\Resolver\JoinQueue\JoinQueueRepositoryOptionsResolver;
use App\Tests\Unit\Slack\Surface\Factory\Resolver\WithOptionAssertions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(JoinQueueRepositoryOptionsResolver::class)]
class JoinQueueRepositoryOptionsResolverTest extends KernelTestCase
{
    use WithOptionAssertions;

    #[Test]
    public function itShouldSupportDeploymentRepositoryArgument(): void
    {
        $resolver = new JoinQueueRepositoryOptionsResolver();

        $this->assertEquals(ModalArgument::DEPLOYMENT_REPOSITORY, $resolver->getSupportedArgument());
    }

    #[Test]
    public function itShouldResolveEmptyArrayIfQueueNotSet(): void
    {
        $result = new JoinQueueRepositoryOptionsResolver()->resolve();

        $this->assertEmpty($result);
    }

    #[Test]
    public function itShouldResolveRepositoriesToOptions(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getRepositories')
            ->willReturn(new ArrayCollection([$repository = $this->createMock(Repository::class)]));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $repository->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $resolver = new JoinQueueRepositoryOptionsResolver();
        $resolver->setQueue($queue);

        $result = $resolver->resolve();

        $this->assertCount(1, $result);
        $this->assertOptionFormedCorrectly($result[0], '1', 'repositoryName');
    }
}
