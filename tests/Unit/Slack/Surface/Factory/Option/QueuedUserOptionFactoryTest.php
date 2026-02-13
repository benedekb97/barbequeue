<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Option;

use App\Entity\Deployment;
use App\Entity\QueuedUser;
use App\Slack\Surface\Factory\Option\QueuedUserOptionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(QueuedUserOptionFactory::class)]
class QueuedUserOptionFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldCreateOptionForQueuedUser(): void
    {
        $queuedUser = $this->createMock(QueuedUser::class);
        $queuedUser->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $factory = new QueuedUserOptionFactory();

        $result = $factory->create($queuedUser, 1);

        $this->assertArrayHasKey('text', $result);
        $this->assertIsArray($result['text']);
        $this->assertArrayHasKey('type', $result['text']);
        $this->assertEquals('plain_text', $result['text']['type']);
        $this->assertArrayHasKey('text', $result['text']);
        $this->assertEquals('#1', $result['text']['text']);
        $this->assertArrayHasKey('value', $result);
        $this->assertEquals('1', $result['value']);
    }

    #[Test]
    public function itShouldCreateOptionForDeployment(): void
    {
        $queuedUser = $this->createMock(Deployment::class);
        $queuedUser->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $queuedUser->expects($this->once())
            ->method('getDescription')
            ->willReturn('description');

        $factory = new QueuedUserOptionFactory();

        $result = $factory->create($queuedUser, 1);

        $this->assertArrayHasKey('text', $result);
        $this->assertIsArray($result['text']);
        $this->assertArrayHasKey('type', $result['text']);
        $this->assertEquals('plain_text', $result['text']['type']);
        $this->assertArrayHasKey('text', $result['text']);
        $this->assertEquals('#1 - description', $result['text']['text']);
        $this->assertArrayHasKey('value', $result);
        $this->assertEquals('1', $result['value']);
    }
}
