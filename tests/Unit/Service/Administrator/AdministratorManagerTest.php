<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Administrator;

use App\Entity\Administrator;
use App\Entity\User;
use App\Entity\Workspace;
use App\Repository\AdministratorRepositoryInterface;
use App\Resolver\UserResolver;
use App\Service\Administrator\AdministratorManager;
use App\Service\Administrator\Exception\AdministratorExistsException;
use App\Service\Administrator\Exception\AdministratorNotFoundException;
use App\Service\Administrator\Exception\UnauthorisedException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorManager::class)]
class AdministratorManagerTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfPassedAdministratorIsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Received null administrator. Make sure you extended AbstractAuthenticatedCommandHandler or AbstractAuthenticatedInteractionHandler');

        $manager = new AdministratorManager(
            $this->createStub(AdministratorRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
            $logger,
            $this->createStub(UserResolver::class),
        );

        $this->expectException(UnauthorisedException::class);

        $manager->addUser('userId', 'teamId', null);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfAdministratorWorkspaceIsNull(): void
    {
        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn(null);

        $this->expectException(UnauthorisedException::class);

        $manager = new AdministratorManager(
            $this->createStub(AdministratorRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $manager->addUser('userId', 'teamId', $administrator);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfSlackIdOfAdministratorWorkspaceIsNull(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn(null);

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $manager = new AdministratorManager(
            $this->createStub(AdministratorRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $this->expectException(UnauthorisedException::class);

        $manager->addUser('userId', 'teamId', $administrator);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfTeamIdPassedDoesNotMatchWorkspaceSlackId(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn('slackId');

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $manager = new AdministratorManager(
            $this->createStub(AdministratorRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $this->expectException(UnauthorisedException::class);

        $manager->addUser('userId', 'teamId', $administrator);
    }

    #[Test]
    public function itShouldThrowAdministratorExistsExceptionIfAdministratorAlreadyExists(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn($slackId = 'slackId');

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->exactly(2))
            ->method('getWorkspace')
            ->willReturn($workspace);

        $existingAdministrator = $this->createStub(Administrator::class);

        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByUserIdAndWorkspace')
            ->with($userId = 'userId', $workspace)
            ->willReturn($existingAdministrator);

        $this->expectException(AdministratorExistsException::class);

        $manager = new AdministratorManager(
            $repository,
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        try {
            $manager->addUser($userId, $slackId, $administrator);
        } catch (AdministratorExistsException $exception) {
            $this->assertSame($existingAdministrator, $exception->getAdministrator());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldReturnNewAdministrator(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn($slackId = 'slackId');

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->exactly(2))
            ->method('getWorkspace')
            ->willReturn($workspace);

        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByUserIdAndWorkspace')
            ->with($userId = 'userId', $workspace)
            ->willReturn(null);

        $persistedAdministrator = null;

        $userResolver = $this->createMock(UserResolver::class);
        $userResolver->expects($this->once())
            ->method('resolve')
            ->with($userId, $workspace)
            ->willReturn($user = $this->createStub(User::class));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($argument) use ($user, $workspace, $administrator, &$persistedAdministrator) {
                $this->assertInstanceOf(Administrator::class, $argument);
                $this->assertSame($user, $argument->getUser());
                $this->assertSame($workspace, $argument->getWorkspace());
                $this->assertSame($administrator, $argument->getAddedBy());

                $persistedAdministrator = $argument;
            });

        $entityManager->expects($this->once())
            ->method('flush')
            ->with();

        $manager = new AdministratorManager(
            $repository,
            $entityManager,
            $this->createStub(LoggerInterface::class),
            $userResolver,
        );

        $result = $manager->addUser($userId, $slackId, $administrator);

        $this->assertSame($persistedAdministrator, $result);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfRemovedByIsNull(): void
    {
        $manager = new AdministratorManager(
            $this->createStub(AdministratorRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $this->expectException(UnauthorisedException::class);

        $manager->removeUser('userId', 'teamId', null);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfRemovedByWorkspaceIsNull(): void
    {
        $manager = new AdministratorManager(
            $this->createStub(AdministratorRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $removedBy = $this->createMock(Administrator::class);
        $removedBy->expects($this->once())
            ->method('getWorkspace')
            ->willReturn(null);

        $this->expectException(UnauthorisedException::class);

        $manager->removeUser('userId', 'teamId', $removedBy);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfRemovedByWorkspaceSlackIdIsNull(): void
    {
        $manager = new AdministratorManager(
            $this->createStub(AdministratorRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn(null);

        $removedBy = $this->createMock(Administrator::class);
        $removedBy->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $this->expectException(UnauthorisedException::class);

        $manager->removeUser('userId', 'teamId', $removedBy);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfRemovedByWorkspaceSlackIdIsDifferentToPassedTeamId(): void
    {
        $manager = new AdministratorManager(
            $this->createStub(AdministratorRepositoryInterface::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn('teamId');

        $removedBy = $this->createMock(Administrator::class);
        $removedBy->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($workspace);

        $this->expectException(UnauthorisedException::class);

        $manager->removeUser('userId', 'differentTeamId', $removedBy);
    }

    #[Test]
    public function itShouldThrowAdministratorNotFoundExceptionIfAdministratorCannotBeFound(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn($teamId = 'teamId');

        $removedBy = $this->createMock(Administrator::class);
        $removedBy->expects($this->exactly(2))
            ->method('getWorkspace')
            ->willReturn($workspace);

        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByUserIdAndWorkspace')
            ->with($userId = 'userId', $workspace)
            ->willReturn(null);

        $this->expectException(AdministratorNotFoundException::class);

        $manager = new AdministratorManager(
            $repository,
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $manager->removeUser($userId, $teamId, $removedBy);
    }

    #[Test]
    public function itShouldThrowUnauthorisedExceptionIfAdministratorAddedByAnotherAdministrator(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn($teamId = 'teamId');

        $removedBy = $this->createMock(Administrator::class);
        $removedBy->expects($this->exactly(2))
            ->method('getWorkspace')
            ->willReturn($workspace);

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('isAddedBy')
            ->with($removedBy)
            ->willReturn(false);

        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByUserIdAndWorkspace')
            ->with($userId = 'userId', $workspace)
            ->willReturn($administrator);

        $this->expectException(UnauthorisedException::class);

        $manager = new AdministratorManager(
            $repository,
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $manager->removeUser($userId, $teamId, $removedBy);
    }

    #[Test]
    public function itShouldRemoveAdministrator(): void
    {
        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getSlackId')
            ->willReturn($teamId = 'teamId');

        $removedBy = $this->createMock(Administrator::class);
        $removedBy->expects($this->exactly(2))
            ->method('getWorkspace')
            ->willReturn($workspace);

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('isAddedBy')
            ->with($removedBy)
            ->willReturn(true);

        $repository = $this->createMock(AdministratorRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOneByUserIdAndWorkspace')
            ->with($userId = 'userId', $workspace)
            ->willReturn($administrator);

        $workspace->expects($this->once())
            ->method('removeAdministrator')
            ->with($administrator)
            ->willReturnSelf();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($administrator);

        $entityManager->expects($this->once())
            ->method('flush');

        $manager = new AdministratorManager(
            $repository,
            $entityManager,
            $this->createStub(LoggerInterface::class),
            $this->createStub(UserResolver::class),
        );

        $manager->removeUser($userId, $teamId, $removedBy);
    }
}
