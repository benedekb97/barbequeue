<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Queue\Common;

use App\Slack\Block\Component\ActionsBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\UnrecognisedQueueActionsBlockFactory;
use App\Slack\Response\Interaction\Factory\Queue\Common\UnrecognisedQueueResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithBlockElementAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UnrecognisedQueueResponseFactory::class)]
class UnrecognisedQueueResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;
    use WithBlockElementAssertions;

    #[Test]
    public function itShouldReturnThreeBlocksIfCalledWithoutActions(): void
    {
        $factory = new UnrecognisedQueueResponseFactory(
            $this->createStub(UnrecognisedQueueActionsBlockFactory::class),
        );

        $response = $factory->create(
            $queueName = 'queueName',
            'teamId',
            withActions: false,
        )->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            sprintf('We couldn\'t find a queue called `%s`.', $queueName),
            $blocks[0],
        );
    }

    #[Test]
    public function itShouldReturnThreeBlocksIfCalledWithActionsButActionsDoNotExist(): void
    {
        $actionsBlockFactory = $this->createMock(UnrecognisedQueueActionsBlockFactory::class);
        $actionsBlockFactory->expects($this->once())
            ->method('create')
            ->with($teamId = 'teamId', $userId = 'userId')
            ->willReturn(null);

        $factory = new UnrecognisedQueueResponseFactory($actionsBlockFactory);

        $response = $factory->create(
            $queueName = 'queueName',
            $teamId,
            $userId,
        )->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(1, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            sprintf('We couldn\'t find a queue called `%s`.', $queueName),
            $blocks[0],
        );
    }

    #[Test]
    public function itShouldReturnFourBlocksIfCalledWithActionsAndActionsExist(): void
    {
        $actionsBlock = $this->createMock(ActionsBlock::class);
        $actionsBlock->expects($this->once())
            ->method('toArray')
            ->willReturn($actionsBlockValue = []);

        $actionsBlockFactory = $this->createMock(UnrecognisedQueueActionsBlockFactory::class);
        $actionsBlockFactory->expects($this->once())
            ->method('create')
            ->with($teamId = 'teamId', $userId = 'userId')
            ->willReturn($actionsBlock);

        $factory = new UnrecognisedQueueResponseFactory($actionsBlockFactory);

        $response = $factory->create(
            $queueName = 'queueName',
            $teamId,
            $userId,
        )->toArray();

        $this->assertArrayHasKey('blocks', $response);
        $this->assertIsArray($blocks = $response['blocks']);
        $this->assertCount(2, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'We couldn\'t find a queue called `'.$queueName.'`. Try these on for size:',
            $blocks[0],
        );

        $this->assertEquals($actionsBlockValue, $blocks[1]);
    }
}
