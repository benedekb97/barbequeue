<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\PrivateMetadata;

use App\Entity\Queue;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;
use App\Slack\Surface\Factory\PrivateMetadata\PopQueuePrivateMetadataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(PopQueuePrivateMetadataFactory::class)]
class PopQueuePrivateMetadataFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowJsonEncodingExceptionIfQueueNotSet(): void
    {
        $this->expectException(JsonEncodingException::class);

        new PopQueuePrivateMetadataFactory()->create();
    }

    #[Test]
    public function itShouldCreatePrivateMetadata(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn('queueName');

        $metadata = new PopQueuePrivateMetadataFactory()
            ->setQueue($queue)
            ->setResponseUrl('responseUrl')
            ->create();

        $metadata = json_decode($metadata, true);

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('queue', $metadata);
        $this->assertEquals('queueName', $metadata['queue']);
        $this->assertArrayHasKey('response_url', $metadata);
        $this->assertEquals('responseUrl', $metadata['response_url']);
        $this->assertArrayHasKey('action', $metadata);
        $this->assertEquals('pop-queue-action', $metadata['action']);
    }
}
