<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DataFixtures\Queue\QueueDomains;
use App\DataFixtures\Queue\Queues;
use App\Entity\Queue;
use App\Repository\QueueRepository;
use App\Repository\QueueRepositoryInterface;
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

        $result = $repository->findOneByNameAndDomain('queueName', 'non-existent-domain');

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfQueueDoesNotExistOnDomain(): void
    {
        /** @var QueueRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueueRepositoryInterface::class);

        $result = $repository->findOneByNameAndDomain('non-existent-queue', QueueDomains::FIRST->value);

        $this->assertNull($result);
    }

    #[Test]
    public function itShouldReturnNullIfDomainDoesNotExist(): void
    {
        /** @var QueueRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueueRepositoryInterface::class);

        $result = $repository->findOneByNameAndDomain(Queues::NO_EXPIRY_USER_LIMIT_ONE->value, 'non-existent-domain');

        $this->assertNull($result);
    }

    #[Test, DataProvider('provideForItShouldReturnQueueIfExists')]
    public function itShouldReturnQueueIfExists(Queues $queueType): void
    {
        /** @var QueueRepositoryInterface $repository */
        $repository = static::getContainer()->get(QueueRepositoryInterface::class);

        $result = $repository->findOneByNameAndDomain($queueType->value, $queueType->getDomain()->value);

        $this->assertInstanceOf(Queue::class, $result);

        $this->assertEquals($queueType->value, $result->getName());
        $this->assertEquals($queueType->getDomain()->value, $result->getDomain());
        $this->assertEquals($queueType->getMaximumEntriesPerUser(), $result->getMaximumEntriesPerUser());
        $this->assertEquals($queueType->getExpiryMinutes(), $result->getExpiryMinutes());
    }

    public static function provideForItShouldReturnQueueIfExists(): iterable
    {
        foreach (Queues::cases() as $queue) {
            yield [$queue];
        }
    }
}
