<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Home;

use App\Entity\Queue;
use App\Entity\Workspace;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersSectionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueueInformationSectionFactory;
use App\Slack\Surface\Factory\Home\UserHomeViewFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithSurfaceAssertions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UserHomeViewFactory::class)]
class UserHomeViewFactoryTest extends KernelTestCase
{
    use WithSurfaceAssertions;
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateSectionsForQueues(): void
    {
        $queue = $this->createStub(Queue::class);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getQueues')
            ->willReturn(new ArrayCollection([$queue]));

        $section = $this->createMock(SectionBlock::class);
        $section->expects($this->exactly(2))
            ->method('toArray')
            ->willReturn([]);

        $queuedUsersSectionsFactory = $this->createMock(QueuedUsersSectionsFactory::class);
        $queuedUsersSectionsFactory->expects($this->once())
            ->method('create')
            ->willReturn([$section]);

        $queueInformationSectionFactory = $this->createMock(QueueInformationSectionFactory::class);
        $queueInformationSectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($section);

        $factory = new UserHomeViewFactory(
            $queuedUsersSectionsFactory,
            $queueInformationSectionFactory,
        );

        $result = $factory->create($userId = 'userId', $workspace);

        $this->assertSame($workspace, $result->getWorkspace());

        $view = $this->assertHomeSurfaceCorrectlyFormed(
            $result->toArray(),
            $userId,
        );

        $this->assertArrayHasKey('blocks', $view);
        $this->assertIsArray($blocks = $view['blocks']);
        $this->assertCount(6, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'To view a list of available commands type `/bbq help`',
            $blocks[0],
        );
        $this->assertDividerBlockCorrectlyFormatted($blocks[1]);
        $this->assertHeaderBlockCorrectlyFormatted('Queues', $blocks[2]);
        $this->assertEquals([], $blocks[3]);
        $this->assertEquals([], $blocks[4]);
        $this->assertDividerBlockCorrectlyFormatted($blocks[5]);
    }
}
