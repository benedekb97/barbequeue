<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Enum\QueueBehaviour;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueueInformationSectionFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueueInformationSectionFactory::class)]
class QueueInformationSectionFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test, DataProvider('provideValues')]
    public function itShouldReturnCorrectString(?int $maxEntries, ?int $expiryTime, string $expectedValue): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $queue->expects($this->once())
            ->method('getMaximumEntriesPerUser')
            ->willReturn($maxEntries);

        $queue->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn($expiryTime);

        $factory = new QueueInformationSectionFactory();

        $result = $factory->create($queue)->toArray();

        $this->assertSectionBlockCorrectlyFormatted($expectedValue, $result);
    }

    public static function provideValues(): array
    {
        return [
            [null, null, '`queueName` queue settings: No user limit. No maximum reservation time.'],
            [5, null, '`queueName` queue settings: Users can join a total of `5` times. No maximum reservation time.'],
            [null, 5, '`queueName` queue settings: No user limit. Maximum reservation time: `5 minutes`.'],
            [5, 5, '`queueName` queue settings: Users can join a total of `5` times. Maximum reservation time: `5 minutes`.'],
        ];
    }

    #[Test, DataProvider('provideValuesForDeploymentQueues')]
    public function itShouldReturnCorrectStringForDeploymentQueues(
        ?int $maxEntries,
        ?int $expiryTime,
        QueueBehaviour $behaviour,
        string $repositoryList,
        string $expected,
    ): void {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $queue->expects($this->once())
            ->method('getMaximumEntriesPerUser')
            ->willReturn($maxEntries);

        $queue->expects($this->once())
            ->method('getExpiryMinutes')
            ->willReturn($expiryTime);

        $queue->expects($this->once())
            ->method('getBehaviour')
            ->willReturn($behaviour);

        $queue->expects($this->once())
            ->method('getPrettyRepositoryList')
            ->willReturn($repositoryList);

        $factory = new QueueInformationSectionFactory();

        $result = $factory->create($queue)->toArray();

        $this->assertSectionBlockCorrectlyFormatted($expected, $result);
    }

    public static function provideValuesForDeploymentQueues(): array
    {
        return [
            [null, null, QueueBehaviour::ALLOW_SIMULTANEOUS, '`repositoryOne`, `repositoryTwo`', '`queueName` queue settings: No user limit. No maximum reservation time. Repositories: `repositoryOne`, `repositoryTwo` Queue behaviour: `allow-simultaneous`'],
            [5, null, QueueBehaviour::ALLOW_JUMPS, '`repositoryOne`', '`queueName` queue settings: Users can join a total of `5` times. No maximum reservation time. Repositories: `repositoryOne` Queue behaviour: `allow-jumps`'],
            [null, 5, QueueBehaviour::ENFORCE_QUEUE, '`repositoryTwo`',  '`queueName` queue settings: No user limit. Maximum reservation time: `5 minutes`. Repositories: `repositoryTwo` Queue behaviour: `enforce-queue`'],
        ];
    }
}
