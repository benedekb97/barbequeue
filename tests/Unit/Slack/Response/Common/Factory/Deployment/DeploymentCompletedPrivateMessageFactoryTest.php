<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Common\Factory\Deployment;

use App\Entity\Deployment;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use App\Slack\Response\PrivateMessage\Factory\Deployment\DeploymentCompletedPrivateMessageFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(DeploymentCompletedPrivateMessageFactory::class)]
class DeploymentCompletedPrivateMessageFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreatePrivateMessage(): void
    {
        $deployment = $this->createMock(Deployment::class);
        $deployment->expects($this->once())
            ->method('getUserLink')
            ->willReturn('userLink');

        $deployment->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getName')
            ->willReturn('repositoryName');

        $user = $this->createStub(User::class);

        $factory = new DeploymentCompletedPrivateMessageFactory();

        $result = $factory->create($deployment, $workspace = $this->createStub(Workspace::class), $repository, $user);

        $this->assertSame($workspace, $result->getWorkspace());
        $this->assertEquals($user, $result->getUser());

        $result = $result->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsString($result['blocks']);

        $blocks = json_decode($result['blocks'], true);

        $this->assertIsArray($blocks);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'userLink has completed their deployment of `description` to `repositoryName`!',
            $blocks[0],
        );
    }
}
