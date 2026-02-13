<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Block;

use App\Entity\Queue;
use App\Slack\BlockElement\Component\ButtonBlockElement;
use App\Slack\Response\Interaction\Factory\Queue\Block\AdministratorQueueActionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\BlockElement\EditQueueButtonFactory;
use App\Slack\Response\Interaction\Factory\Queue\BlockElement\PopQueueButtonFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorQueueActionsFactory::class)]
class AdministratorQueueActionsFactoryTest extends KernelTestCase
{
    #[Test]
    public function itShouldReturnActionsBlockWithTwoButtons(): void
    {
        $queue = $this->createMock(Queue::class);
        $queue->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $editButton = $this->createMock(ButtonBlockElement::class);
        $editButton->expects($this->once())
            ->method('toArray')
            ->willReturn($editButtonValue = ['edit']);

        $editButtonFactory = $this->createMock(EditQueueButtonFactory::class);
        $editButtonFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($editButton);

        $popButton = $this->createMock(ButtonBlockElement::class);
        $popButton->expects($this->once())
            ->method('toArray')
            ->willReturn($popButtonValue = ['pop']);

        $popButtonFactory = $this->createMock(PopQueueButtonFactory::class);
        $popButtonFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($popButton);

        $factory = new AdministratorQueueActionsFactory($editButtonFactory, $popButtonFactory);

        $result = $factory->create($queue)->toArray();

        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('actions', $result['type']);

        $this->assertArrayHasKey('block_id', $result);
        $this->assertEquals('queue_admin_action_1', $result['block_id']);

        $this->assertArrayHasKey('elements', $result);
        $this->assertIsArray($elements = $result['elements']);
        $this->assertCount(2, $elements);

        $this->assertEquals($editButtonValue, $elements[0]);
        $this->assertEquals($popButtonValue, $elements[1]);
    }
}
