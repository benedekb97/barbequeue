<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\PrivateMetadata;

use App\Entity\DeploymentQueue;
use App\Entity\Queue;
use App\Slack\Surface\Factory\PrivateMetadata\EditQueuePrivateMetadataFactory;
use App\Slack\Surface\Factory\PrivateMetadata\Exception\JsonEncodingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(EditQueuePrivateMetadataFactory::class)]
class EditQueuePrivateMetadataFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldThrowJsonEncodingExceptionIfQueueNotSet(): void
    {
        $factory = new EditQueuePrivateMetadataFactory();

        $this->expectException(JsonEncodingException::class);

        try {
            $factory->create();
        } catch (JsonEncodingException $exception) {
            $this->assertEquals('Could not encode private metadata: missing queue', $exception->getMessage());

            throw $exception;
        }
    }

    #[Test]
    public function itShouldEncodeQueueIdActionAndResponseUrl(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $result = new EditQueuePrivateMetadataFactory()->setQueue($queue)->setResponseUrl('responseUrl')->create();

        $result = json_decode($result, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('action', $result);
        $this->assertEquals('edit-queue', $result['action']);
        $this->assertArrayHasKey('queue', $result);
        $this->assertEquals(1, $result['queue']);
        $this->assertArrayHasKey('response_url', $result);
        $this->assertEquals('responseUrl', $result['response_url']);
    }

    #[Test]
    public function itShouldEncodeDeploymentQueueIdActionAndResponseUrl(): void
    {
        $queue = $this->createMock(DeploymentQueue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $result = new EditQueuePrivateMetadataFactory()->setQueue($queue)->setResponseUrl('responseUrl')->create();

        $result = json_decode($result, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('action', $result);
        $this->assertEquals('edit-queue-deployment', $result['action']);
        $this->assertArrayHasKey('queue', $result);
        $this->assertEquals(1, $result['queue']);
        $this->assertArrayHasKey('response_url', $result);
        $this->assertEquals('responseUrl', $result['response_url']);
    }
}
