<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DataFixtures\Queue\Queues;
use App\DataFixtures\Queue\Workspaces;
use App\Entity\Queue;
use App\Entity\Workspace;
use App\Repository\QueueRepository;
use App\Repository\QueueRepositoryInterface;
use App\Repository\WorkspaceRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueRepository::class)]
class QueueRepositoryTest extends KernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    #[Test]
    public function itShouldReturnNullIfNoQueuesExistOnDomain(): void
    {
        /** @var QueueRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueueRepositoryInterface::class);

        $result = $repository->findOneByNameAndTeamId('queueName', 'non-existent-teamId');

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfQueueDoesNotExistOnDomain(): void
    {
        /** @var QueueRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueueRepositoryInterface::class);

        $result = $repository->findOneByNameAndTeamId('non-existent-queue', Workspaces::FIRST->value);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfDomainDoesNotExist(): void
    {
        /** @var QueueRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueueRepositoryInterface::class);

        $result = $repository->findOneByNameAndTeamId(Queues::NO_EXPIRY_USER_LIMIT_ONE->value, 'non-existent-team-id');

        $this->assertNull($result);
    }

    #[Test, DataProvider('provideForItShouldReturnQueueIfExists')]
    public function itShouldReturnQueueIfExists(Queues $queueType): void
    {
        /** @var QueueRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueueRepositoryInterface::class);

        $result = $repository->findOneByNameAndTeamId($queueType->value, $queueType->getTeamId()->value);

        $this->assertInstanceOf(Queue::class, $result);

        $this->assertEquals($queueType->value, $result->getName());
        $this->assertEquals($queueType->getTeamId()->value, $result->getWorkspace()?->getSlackId());
        $this->assertEquals($queueType->getMaximumEntriesPerUser(), $result->getMaximumEntriesPerUser());
        $this->assertEquals($queueType->getExpiryMinutes(), $result->getExpiryMinutes());
    }

    public static function provideForItShouldReturnQueueIfExists(): iterable
    {
        foreach (Queues::cases() as $queue) {
            yield [$queue];
        }
    }

    #[Test]
    public function itShouldReturnQueueIfExistsOnWorkspace(): void
    {
        /** @var QueueRepositoryInterface $repository */
        $repository = self::getContainer()->get(QueueRepositoryInterface::class);

        $result = $repository->findByTeamId(Workspaces::FIRST->value);

        $this->assertNotEmpty($result);
        $this->assertCount(count(Workspaces::FIRST->getQueues()), $result);
    }

    #[Test]
    public function itShouldFindQueueByNameAndWorkspace(): void
    {
        /** @var WorkspaceRepositoryInterface $workspaceRepository */
        $workspaceRepository = self::getContainer()->get(WorkspaceRepositoryInterface::class);

        /** @var Workspace $workspace */
        $workspace = $workspaceRepository->findOneBy(['slackId' => Workspaces::FIRST->value]);

        /** @var QueueRepositoryInterface $queueRepository */
        $queueRepository = self::getContainer()->get(QueueRepositoryInterface::class);

        $queue = $queueRepository->findOneByNameAndWorkspace(
            Queues::FIFTEEN_MINUTE_EXPIRY_USER_LIMIT_THREE->value,
            $workspace,
        );

        $this->assertNotNull($queue);
    }

    #[Test]
    public function itShouldReturnNullIfWorkspaceIsNull(): void
    {
        $repository = new QueueRepository($this->createStub(ManagerRegistry::class));

        $result = $repository->findOneByNameAndWorkspace('name', null);

        $this->assertNull($result);
    }
}
