<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Provider;

use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\UserRepositoryInterface;
use App\Repository\WorkspaceRepositoryInterface;
use App\Resolver\UserResolver;
use App\Security\Provider\SlackUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(SlackUserProvider::class)]
class SlackUserProviderTest extends KernelTestCase
{
    #[Test]
    public function itShouldCallEntityManagerRefreshOnRefreshUser(): void
    {
        $provider = new SlackUserProvider(
            $entityManager = $this->createMock(EntityManagerInterface::class),
            $this->createStub(UserRepositoryInterface::class),
            $this->createStub(WorkspaceRepositoryInterface::class),
            $this->createStub(UserResolver::class),
        );

        $entityManager->expects($this->once())
            ->method('refresh')
            ->with($user = $this->createStub(User::class));

        $result = $provider->refreshUser($user);

        $this->assertSame($user, $result);
    }

    #[Test]
    public function itShouldThrowUnsupportedUserExceptionIfGenericUserInterfacePassed(): void
    {
        $provider = new SlackUserProvider(
            $entityManager = $this->createMock(EntityManagerInterface::class),
            $this->createStub(UserRepositoryInterface::class),
            $this->createStub(WorkspaceRepositoryInterface::class),
            $this->createStub(UserResolver::class),
        );

        $entityManager->expects($this->once())
            ->method('refresh')
            ->with($user = $this->createStub(UserInterface::class));

        $this->expectException(UnsupportedUserException::class);

        $provider->refreshUser($user);
    }

    #[Test]
    public function itShouldSupportUserClass(): void
    {
        $provider = new SlackUserProvider(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UserRepositoryInterface::class),
            $this->createStub(WorkspaceRepositoryInterface::class),
            $this->createStub(UserResolver::class),
        );

        $this->assertTrue($provider->supportsClass(User::class));
    }

    #[Test]
    public function itShouldNotSupportGenericUserInterface(): void
    {
        $provider = new SlackUserProvider(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UserRepositoryInterface::class),
            $this->createStub(WorkspaceRepositoryInterface::class),
            $this->createStub(UserResolver::class),
        );

        $this->assertFalse($provider->supportsClass(UserInterface::class));
    }

    #[Test]
    public function itShouldThrowUserNotFoundExceptionIfUserIdKeyNotInAttributesArray(): void
    {
        $provider = new SlackUserProvider(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UserRepositoryInterface::class),
            $this->createStub(WorkspaceRepositoryInterface::class),
            $this->createStub(UserResolver::class),
        );

        $this->expectException(UserNotFoundException::class);

        $provider->loadUserByIdentifier('');
    }

    #[Test]
    public function itShouldThrowUserNotFoundExceptionIfTeamIdKeyNotInAttributesArray(): void
    {
        $provider = new SlackUserProvider(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UserRepositoryInterface::class),
            $this->createStub(WorkspaceRepositoryInterface::class),
            $this->createStub(UserResolver::class),
        );

        $this->expectException(UserNotFoundException::class);

        $attributes = [
            'https://slack.com/user_id' => 'userId',
        ];

        $provider->loadUserByIdentifier('', $attributes);
    }

    #[Test]
    public function itShouldReturnUserIfRepositoryReturnsUser(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBySlackIdAndWorkspaceSlackId')
            ->with($userId = 'userId', $workspaceId = 'workspaceId')
            ->willReturn($user = $this->createStub(User::class));

        $provider = new SlackUserProvider(
            $this->createStub(EntityManagerInterface::class),
            $repository,
            $this->createStub(WorkspaceRepositoryInterface::class),
            $this->createStub(UserResolver::class),
        );

        $attributes = [
            'https://slack.com/user_id' => $userId,
            'https://slack.com/team_id' => $workspaceId,
        ];

        $result = $provider->loadUserByIdentifier($userId, $attributes);

        $this->assertSame($user, $result);
    }

    #[Test]
    public function itShouldThrowAccessDeniedExceptionIfWorkspaceDoesNotExist(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBySlackIdAndWorkspaceSlackId')
            ->with($userId = 'userId', $workspaceId = 'workspaceId')
            ->willReturn(null);

        $workspaceRepository = $this->createMock(WorkspaceRepositoryInterface::class);
        $workspaceRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $workspaceId])
            ->willReturn(null);

        $provider = new SlackUserProvider(
            $this->createStub(EntityManagerInterface::class),
            $repository,
            $workspaceRepository,
            $this->createStub(UserResolver::class),
        );

        $attributes = [
            'https://slack.com/user_id' => $userId,
            'https://slack.com/team_id' => $workspaceId,
        ];

        $this->expectException(AccessDeniedHttpException::class);

        $provider->loadUserByIdentifier($userId, $attributes);
    }

    #[Test]
    public function itShouldPersistNewUserIfWorkspaceExists(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneBySlackIdAndWorkspaceSlackId')
            ->with($userId = 'userId', $workspaceId = 'workspaceId')
            ->willReturn(null);

        $workspaceRepository = $this->createMock(WorkspaceRepositoryInterface::class);
        $workspaceRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['slackId' => $workspaceId])
            ->willReturn($workspace = $this->createStub(Workspace::class));

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->expects($this->once())
            ->method('resolve')
            ->with($userId, $workspace)
            ->willReturn($user = $this->createStub(User::class));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $entityManager->expects($this->once())
            ->method('flush');

        $provider = new SlackUserProvider(
            $entityManager,
            $repository,
            $workspaceRepository,
            $userResolver,
        );

        $attributes = [
            'https://slack.com/user_id' => $userId,
            'https://slack.com/team_id' => $workspaceId,
        ];

        $result = $provider->loadUserByIdentifier($userId, $attributes);

        $this->assertSame($user, $result);
    }
}
