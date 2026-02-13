<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Common\Factory\Deployment;

use App\Entity\Deployment;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use App\Slack\Response\PrivateMessage\Factory\Deployment\StartDeploymentMessageFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(StartDeploymentMessageFactory::class)]
class StartDeploymentMessageFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateMessageWithExpiry(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createStub(User::class));

        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $deployment->expects($this->exactly(2))
            ->method('getExpiresAt')
            ->willReturn($expiry = $this->createMock(CarbonImmutable::class));

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $expiry->expects($this->once())
            ->method('diffInMinutes')
            ->with(null, true)
            ->willReturn(10.0);

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $factory = new StartDeploymentMessageFactory();
        $result = $factory->create($deployment);

        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertEquals($user, $result->getUser());

        $result = $result->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsString($result['blocks']);

        $blocks = json_decode($result['blocks'], true);

        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'You can start deploying `description` to `repositoryName` now! You have `10 minutes` before you are removed from the front of the queue.',
            $blocks[0],
        );
    }

    #[Test]
    public function itShouldCreateMessageWithOutExpiry(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createStub(User::class));

        $deployment->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue = $this->createMock(Queue::class));

        $deployment->expects($this->once())
            ->method('getExpiresAt')
            ->willReturn(null);

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $deployment->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository = $this->createMock(Repository::class));

        $queue->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $factory = new StartDeploymentMessageFactory();
        $result = $factory->create($deployment);

        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertEquals($user, $result->getUser());

        $result = $result->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsString($result['blocks']);

        $blocks = json_decode($result['blocks'], true);

        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'You can start deploying `description` to `repositoryName` now!',
            $blocks[0],
        );
    }
}
