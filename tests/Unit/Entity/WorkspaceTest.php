<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Administrator;
use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\User;
use App\Entity\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Workspace::class)]
class WorkspaceTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnPassedValues(): void
    {
        $workspace = new Workspace()
            ->setSlackId($slackId = 'slackId')
            ->setBotToken($botToken = 'botToken')
            ->setName($name = 'name');

        $setWorkspaceCallCount = 0;

        $user = $this->createMock(User::class);
        $user->expects($this->exactly(2))
            ->method('setWorkspace')
            ->willReturnCallback(function ($argument) use ($workspace, &$setWorkspaceCallCount, $user) {
                if (1 === ++$setWorkspaceCallCount) {
                    $this->assertSame($workspace, $argument);
                } else {
                    $this->assertNull($argument);
                }

                return $user;
            });

        $user->expects($this->exactly(2))
            ->method('getSlackId')
            ->willReturn($userId = 'userId');

        $workspace->addUser($user);

        $this->assertCount(1, $workspace->getUsers());
        $this->assertSame($user, $workspace->getUsers()->first());

        $this->assertTrue($workspace->hasUserWithId($userId));
        $this->assertSame($user, $workspace->getUserById($userId));

        $workspace->removeUser($user);

        $this->assertCount(0, $workspace->getUsers());

        $this->assertSame($slackId, $workspace->getSlackId());
        $this->assertSame($botToken, $workspace->getBotToken());
        $this->assertSame($name, $workspace->getName());

        /** @var ArrayCollection<int, Administrator> $administrators */
        $administrators = new ArrayCollection();

        $callCount = 0;

        for ($i = 0; $i < 3; ++$i) {
            $administrators->add($administrator = $this->createMock(Administrator::class));
            $administrator->expects($this->exactly(2))
                ->method('setWorkspace')
                ->willReturnCallback(function ($argument) use (&$callCount, $administrator, $workspace) {
                    if (++$callCount < 4) {
                        $this->assertInstanceOf(Workspace::class, $argument);
                        $this->assertEquals($workspace, $argument);

                        return $administrator;
                    }

                    $this->assertNull($argument);

                    return $administrator;
                });

            $workspace->addAdministrator($administrator);
        }

        foreach ($administrators as $administrator) {
            $this->assertTrue(
                $workspace->getAdministrators()->contains($administrator),
            );
        }

        foreach ($workspace->getAdministrators() as $administrator) {
            $this->assertTrue(
                $administrators->contains($administrator),
            );
        }

        foreach ($workspace->getAdministrators() as $administrator) {
            $workspace->removeAdministrator($administrator);
        }

        $this->assertCount(0, $workspace->getAdministrators());

        /** @var ArrayCollection<int, Queue> $queues */
        $queues = new ArrayCollection();

        $callCount = 0;

        for ($i = 0; $i < 3; ++$i) {
            $queues->add($queue = $this->createMock(Queue::class));
            $queue->expects($this->exactly(2))
                ->method('setWorkspace')
                ->willReturnCallback(function ($argument) use (&$callCount, $queue, $workspace) {
                    if (++$callCount < 4) {
                        $this->assertInstanceOf(Workspace::class, $argument);
                        $this->assertEquals($workspace, $argument);

                        return $queue;
                    }

                    $this->assertNull($argument);

                    return $queue;
                });

            $workspace->addQueue($queue);
        }

        foreach ($queues as $queue) {
            $this->assertTrue(
                $workspace->getQueues()->contains($queue),
            );
        }

        foreach ($workspace->getQueues() as $queue) {
            $this->assertTrue(
                $queues->contains($queue),
            );
        }

        foreach ($workspace->getQueues() as $queue) {
            $workspace->removeQueue($queue);
        }

        $this->assertCount(0, $workspace->getQueues());

        /** @var ArrayCollection<int, Repository> $repositories */
        $repositories = new ArrayCollection();

        $callCount = 0;

        for ($i = 0; $i < 3; ++$i) {
            $repositories->add($repository = $this->createMock(Repository::class));
            $repository->expects($this->exactly(2))
                ->method('setWorkspace')
                ->willReturnCallback(function ($argument) use (&$callCount, $repository, $workspace) {
                    if (++$callCount < 4) {
                        $this->assertInstanceOf(Workspace::class, $argument);
                        $this->assertEquals($workspace, $argument);

                        return $repository;
                    }

                    $this->assertNull($argument);

                    return $repository;
                });

            $workspace->addRepository($repository);
        }

        foreach ($repositories as $repository) {
            $this->assertTrue(
                $workspace->getRepositories()->contains($repository),
            );
        }

        foreach ($workspace->getRepositories() as $repository) {
            $this->assertTrue(
                $repositories->contains($repository),
            );
        }

        foreach ($workspace->getRepositories() as $repository) {
            $workspace->removeRepository($repository);
        }

        $this->assertCount(0, $workspace->getRepositories());
    }

    #[Test]
    public function itShouldReturnTrueOnHasAdministratorWithUserIdIfAdministratorExists(): void
    {
        $workspace = new Workspace();

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId = 'userId');

        $administrator->expects($this->once())
            ->method('setWorkspace')
            ->with($workspace)
            ->willReturnSelf();

        $workspace->addAdministrator($administrator);

        $this->assertTrue($workspace->hasAdministratorWithUserId($userId));
    }

    #[Test]
    public function itShouldReturnFalseOnHasAdministratorWithUseridIfAdministratorDoesNotExist(): void
    {
        $workspace = new Workspace();

        $administrator = $this->createMock(Administrator::class);
        $administrator->expects($this->once())
            ->method('getUserId')
            ->willReturn('userId');

        $administrator->expects($this->once())
            ->method('setWorkspace')
            ->with($workspace)
            ->willReturnSelf();

        $workspace->addAdministrator($administrator);

        $this->assertFalse($workspace->hasAdministratorWithUserId('differentUserId'));
    }
}
