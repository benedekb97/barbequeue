<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Surface\Factory\Home;

use App\Entity\Queue;
use App\Entity\Repository;
use App\Entity\Workspace;
use App\Slack\Block\Component\ActionsBlock;
use App\Slack\Block\Component\SectionBlock;
use App\Slack\Response\Interaction\Factory\Queue\Block\AdministratorQueueActionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueuedUsersSectionsFactory;
use App\Slack\Response\Interaction\Factory\Queue\Block\QueueInformationSectionFactory;
use App\Slack\Response\Interaction\Factory\Repository\Deployments\RepositoryDeploymentsSectionsFactory;
use App\Slack\Surface\Factory\Home\AdministratorHomeViewFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithSurfaceAssertions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(AdministratorHomeViewFactory::class)]
class AdministratorHomeViewFactoryTest extends KernelTestCase
{
    use WithSurfaceAssertions;
    use WithBlockAssertions;

    #[Test]
    public function itShouldCreateHomeSurface(): void
    {
        $queue = $this->createStub(Queue::class);

        $repository = $this->createStub(Repository::class);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getQueues')
            ->willReturn(new ArrayCollection([$queue]));

        $workspace->expects($this->once())
            ->method('getRepositories')
            ->willReturn(new ArrayCollection([$repository]));

        $section = $this->createMock(SectionBlock::class);
        $section->expects($this->exactly(2))
            ->method('toArray')
            ->willReturn([]);

        $queuedUsersSectionsFactory = $this->createMock(QueuedUsersSectionsFactory::class);
        $queuedUsersSectionsFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn([$section]);

        $queueInformationSectionFactory = $this->createMock(QueueInformationSectionFactory::class);
        $queueInformationSectionFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($section);

        $action = $this->createMock(ActionsBlock::class);
        $action->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $administratorQueueActionsFactory = $this->createMock(AdministratorQueueActionsFactory::class);
        $administratorQueueActionsFactory->expects($this->once())
            ->method('create')
            ->with($queue)
            ->willReturn($action);

        $repositoryDeploymentSectionsFactory = $this->createMock(RepositoryDeploymentsSectionsFactory::class);
        $repositoryDeploymentSectionsFactory->expects($this->once())
            ->method('create')
            ->with($repository)
            ->willReturn([
                $section = $this->createMock(SectionBlock::class),
            ]);

        $section->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $factory = new AdministratorHomeViewFactory(
            $queuedUsersSectionsFactory,
            $queueInformationSectionFactory,
            $administratorQueueActionsFactory,
            $repositoryDeploymentSectionsFactory,
        );

        $result = $factory->create($userId = 'userId', $workspace);

        $this->assertSame($workspace, $result->getWorkspace());

        $view = $this->assertHomeSurfaceCorrectlyFormed(
            $result->toArray(),
            $userId,
        );

        $this->assertArrayHasKey('blocks', $view);
        $this->assertIsArray($blocks = $view['blocks']);
        $this->assertCount(10, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'To view a list of available commands type `/bbq-admin help` or `/bbq help`',
            $blocks[0],
        );
        $this->assertDividerBlockCorrectlyFormatted($blocks[1]);
        $this->assertHeaderBlockCorrectlyFormatted('Repositories', $blocks[2]);
        $this->assertEquals([], $blocks[3]);
        $this->assertDividerBlockCorrectlyFormatted($blocks[4]);
        $this->assertHeaderBlockCorrectlyFormatted('Queues', $blocks[5]);

        $this->assertEquals([], $blocks[6]);
        $this->assertEquals([], $blocks[7]);
        $this->assertEquals([], $blocks[8]);

        $this->assertDividerBlockCorrectlyFormatted($blocks[9]);
    }
}
