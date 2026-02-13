<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\PrivateMetadata;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;
use App\Slack\Surface\Factory\PrivateMetadata\JoinQueuePrivateMetadataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(JoinQueuePrivateMetadataFactory::class)]
class JoinQueuePrivateMetadataFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowJsonEncodingExceptionIfQueueNotSet(): void
    {
        $factory = new JoinQueuePrivateMetadataFactory();

        $this->expectException(JsonEncodingException::class);

        $factory->create();
    }

    #[Test]
    public function itShouldThrowJsonEncodingExceptionIfQueueNotDeploymentQueue(): void
    {
        $this->expectException(JsonEncodingException::class);

        new JoinQueuePrivateMetadataFactory()->setQueue($this->createStub(Queue::class))->create();
    }

    #[Test]
    public function itShouldEncodePrivateMetadata(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getName')
            ->willReturn($queueName = 'queueName');

        $metadata = new JoinQueuePrivateMetadataFactory()
            ->setQueue($queue)
            ->setResponseUrl($responseUrl = 'responseUrl')
            ->create();

        $metadata = json_decode($metadata, true);

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('action', $metadata);
        $this->assertEquals('join-queue-deployment', $metadata['action']);

        $this->assertArrayHasKey('join_queue_name', $metadata);
        $this->assertEquals($queueName, $metadata['join_queue_name']);

        $this->assertArrayHasKey('response_url', $metadata);
        $this->assertEquals($responseUrl, $metadata['response_url']);
    }
}
