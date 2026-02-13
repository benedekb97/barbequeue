<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Home;

use App\Entity\Workspace;
use App\Repository\WorkspaceRepositoryInterface;
use App\Slack\Surface\Component\HomeSurface;
use App\Slack\Surface\Factory\Exception\WorkspaceNotFoundException;
use App\Slack\Surface\Factory\Home\AdministratorHomeViewFactory;
use App\Slack\Surface\Factory\Home\AdministratorWelcomeHomeViewFactory;
use App\Slack\Surface\Factory\Home\HomeViewFactory;
use App\Slack\Surface\Factory\Home\UserHomeViewFactory;
use App\Slack\Surface\Factory\Home\UserWelcomeHomeViewFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(HomeViewFactory::class)]
class HomeViewFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowWorkspaceNotFoundExceptionIfRepositoryReturnsNull(): void
    {
        $repository = $this->createMock(WorkspaceRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn(null);

        $factory = new HomeViewFactory(
            $repository,
            $this->createStub(UserWelcomeHomeViewFactory::class),
            $this->createStub(AdministratorWelcomeHomeViewFactory::class),
            $this->createStub(UserHomeViewFactory::class),
            $this->createStub(AdministratorHomeViewFactory::class),
        );

        $this->expectException(WorkspaceNotFoundException::class);

        $factory->create('userId', $teamId, false);
    }

    #[Test]
    public function itShouldCreateAdministratorWelcomeHomeViewIfFirstTimeAdministrator(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('hasAdministratorWithUserId')
            ->with($userId = 'userId')
            ->willReturn(true);

        $repository = $this->createMock(WorkspaceRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn($workspace);

        $administratorWelcomeFactory = $this->createMock(AdministratorWelcomeHomeViewFactory::class);
        $administratorWelcomeFactory->expects($this->once())
            ->method('create')
            ->with($userId, $workspace)
            ->willReturn($surface = $this->createStub(HomeSurface::class));

        $administratorFactory = $this->createMock(AdministratorHomeViewFactory::class);
        $administratorFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $userWelcomeFactory = $this->createMock(UserWelcomeHomeViewFactory::class);
        $userWelcomeFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $userFactory = $this->createMock(UserHomeViewFactory::class);
        $userFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $factory = new HomeViewFactory(
            $repository,
            $userWelcomeFactory,
            $administratorWelcomeFactory,
            $userFactory,
            $administratorFactory,
        );

        $result = $factory->create($userId, $teamId, true);

        $this->assertSame($result, $surface);
    }

    #[Test]
    public function itShouldCreateAdministratorHomeViewIfNotFirstTimeAdministrator(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->exactly(2))
            ->method('hasAdministratorWithUserId')
            ->with($userId = 'userId')
            ->willReturn(true);

        $repository = $this->createMock(WorkspaceRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn($workspace);

        $administratorWelcomeFactory = $this->createMock(AdministratorWelcomeHomeViewFactory::class);
        $administratorWelcomeFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorFactory = $this->createMock(AdministratorHomeViewFactory::class);
        $administratorFactory->expects($this->once())
            ->method('create')
            ->with($userId, $workspace)
            ->willReturn($surface = $this->createStub(HomeSurface::class));

        $userWelcomeFactory = $this->createMock(UserWelcomeHomeViewFactory::class);
        $userWelcomeFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $userFactory = $this->createMock(UserHomeViewFactory::class);
        $userFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $factory = new HomeViewFactory(
            $repository,
            $userWelcomeFactory,
            $administratorWelcomeFactory,
            $userFactory,
            $administratorFactory,
        );

        $result = $factory->create($userId, $teamId, false);

        $this->assertSame($result, $surface);
    }

    #[Test]
    public function itShouldCreateUserWelcomeHomeViewIfFirstTimeNonAdministrator(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->exactly(2))
            ->method('hasAdministratorWithUserId')
            ->with($userId = 'userId')
            ->willReturn(false);

        $repository = $this->createMock(WorkspaceRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn($workspace);

        $administratorWelcomeFactory = $this->createMock(AdministratorWelcomeHomeViewFactory::class);
        $administratorWelcomeFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorFactory = $this->createMock(AdministratorHomeViewFactory::class);
        $administratorFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $userWelcomeFactory = $this->createMock(UserWelcomeHomeViewFactory::class);
        $userWelcomeFactory->expects($this->once())
            ->method('create')
            ->with($userId, $workspace)
            ->willReturn($surface = $this->createStub(HomeSurface::class));

        $userFactory = $this->createMock(UserHomeViewFactory::class);
        $userFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $factory = new HomeViewFactory(
            $repository,
            $userWelcomeFactory,
            $administratorWelcomeFactory,
            $userFactory,
            $administratorFactory,
        );

        $result = $factory->create($userId, $teamId, true);

        $this->assertSame($result, $surface);
    }

    #[Test]
    public function itShouldCreateUserHomeViewIfNotFirstTimeNonAdministrator(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->exactly(2))
            ->method('hasAdministratorWithUserId')
            ->with($userId = 'userId')
            ->willReturn(false);

        $repository = $this->createMock(WorkspaceRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $teamId = 'teamId'])
            ->willReturn($workspace);

        $administratorWelcomeFactory = $this->createMock(AdministratorWelcomeHomeViewFactory::class);
        $administratorWelcomeFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $administratorFactory = $this->createMock(AdministratorHomeViewFactory::class);
        $administratorFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $userWelcomeFactory = $this->createMock(UserWelcomeHomeViewFactory::class);
        $userWelcomeFactory->expects($this->never())
            ->method('create')
            ->withAnyParameters();

        $userFactory = $this->createMock(UserHomeViewFactory::class);
        $userFactory->expects($this->once())
            ->method('create')
            ->with($userId, $workspace)
            ->willReturn($surface = $this->createStub(HomeSurface::class));

        $factory = new HomeViewFactory(
            $repository,
            $userWelcomeFactory,
            $administratorWelcomeFactory,
            $userFactory,
            $administratorFactory,
        );

        $result = $factory->create($userId, $teamId, false);

        $this->assertSame($result, $surface);
    }
}
