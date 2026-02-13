<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Common\Factory\Deployment;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use App\Slack\Response\PrivateMessage\Factory\Deployment\CompletedDeploymentMessageFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(CompletedDeploymentMessageFactory::class)]
class CompletedDeploymentMessageFactoryTest extends KernelTestCase
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
            ->method('getExpiryMinutes')
            ->willReturn(10);

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $factory = new CompletedDeploymentMessageFactory();
        $result = $factory->create($deployment, $workspace = $this->createStub(Workspace::class), $repository);

        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertEquals($user, $result->getUser());

        $result = $result->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsString($result['blocks']);

        $blocks = json_decode($result['blocks'], true);

        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'Your deployment of `description` to `repositoryName` has completed automatically after 10 minutes.',
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
            ->method('getExpiryMinutes')
            ->willReturn(null);

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $factory = new CompletedDeploymentMessageFactory();
        $result = $factory->create($deployment, $workspace = $this->createStub(Workspace::class), $repository);

        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertEquals($user, $result->getUser());

        $result = $result->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsString($result['blocks']);

        $blocks = json_decode($result['blocks'], true);

        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);
        $this->assertSectionBlockCorrectlyFormatted(
            'Your deployment of `description` to `repositoryName` has been marked as completed.',
            $blocks[0],
        );
    }
}
