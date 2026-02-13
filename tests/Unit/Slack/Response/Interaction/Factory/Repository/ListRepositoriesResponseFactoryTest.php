<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository;

use App\Entity\Repository;
use App\Entity\Workspace;
use App\Slack\Response\Interaction\Factory\Repository\ListRepositoriesResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithTableAssertions;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ListRepositoriesResponseFactory::class)]
class ListRepositoriesResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;
    use WithTableAssertions;

    #[Test]
    public function itShouldCreateSlackInteractionResponse(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())
            ->method('getName')
            ->willReturn($name = 'name');

        $repository->expects($this->once())
            ->method('getUrl')
            ->willReturn($url = 'url');

        $secondRepository = $this->createMock(Repository::class);
        $secondRepository->expects($this->once())
            ->method('getName')
            ->willReturn($secondName = 'secondName');

        $secondRepository->expects($this->once())
            ->method('getUrl')
            ->willReturn(null);

        $workspace = $this->createMock(Workspace::class);
        $workspace->expects($this->once())
            ->method('getRepositories')
            ->willReturn(new ArrayCollection([$repository, $secondRepository]));

        $factory = new ListRepositoriesResponseFactory();

        $result = $factory->create($workspace)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'A list of your currently added repositories:',
            $blocks[0],
        );

        $table = $this->assertTableRowCount($blocks[1], 3);

        $header = $this->getTableRow($table, 0);

        $this->assertRawTextCell($this->getRowCell($header, 0), 'Name');
        $this->assertRawTextCell($this->getRowCell($header, 1), 'URL');

        $firstRow = $this->getTableRow($table, 1);

        $this->assertRawTextCell($this->getRowCell($firstRow, 0), $name);
        $this->assertLinkCell($this->getRowCell($firstRow, 1), $url);

        $secondRow = $this->getTableRow($table, 2);

        $this->assertRawTextCell($this->getRowCell($secondRow, 0), $secondName);
        $this->assertItalicTextCell($this->getRowCell($secondRow, 1), 'No URL set');
    }
}
