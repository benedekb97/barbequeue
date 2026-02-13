<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\OAuth\Resolver;

use App\Entity\Workspace;
use App\Repository\WorkspaceRepositoryInterface;
use App\Service\OAuth\OAuthAccessResponse;
use App\Service\OAuth\Resolver\WorkspaceResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(WorkspaceResolver::class)]
class WorkspaceResolverTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnWorkspaceFromRepository(): void
    {
        $response = $this->createMock(OAuthAccessResponse::class);
        $response->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $response->expects($this->once())
            ->method('getTeamName')
            ->willReturn($teamName = 'teamName');

        $response->expects($this->once())
            ->method('getAccessToken')
            ->willReturn($accessToken = 'accessToken');

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('setSlackId')
            ->with($teamId)
            ->willReturnSelf();

        $workspace->expects($this->once())
            ->method('setName')
            ->with($teamName)
            ->willReturnSelf();

        $workspace->expects($this->once())
            ->method('setBotToken')
            ->with($accessToken)
            ->willReturnSelf();

        $repository = $this->createMock(WorkspaceRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId])
            ->willReturn($workspace);

        $resolver = new WorkspaceResolver($repository);

        $result = $resolver->resolve($response);

        $this->assertSame($workspace, $result);
    }

    #[Test]
    public function itShouldReturnNewWorkspaceIfRepositoryReturnsNull(): void
    {
        $response = $this->createMock(OAuthAccessResponse::class);
        $response->expects($this->exactly(2))
            ->method('getTeamId')
            ->willReturn($teamId = 'teamId');

        $response->expects($this->once())
            ->method('getTeamName')
            ->willReturn($teamName = 'teamName');

        $response->expects($this->once())
            ->method('getAccessToken')
            ->willReturn($accessToken = 'accessToken');

        $repository = $this->createMock(WorkspaceRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId])
            ->willReturn(null);

        $resolver = new WorkspaceResolver($repository);

        $result = $resolver->resolve($response);

        $this->assertEquals($teamId, $result->getSlackId());
        $this->assertEquals($teamName, $result->getName());
        $this->assertEquals($accessToken, $result->getBotToken());
    }
}
