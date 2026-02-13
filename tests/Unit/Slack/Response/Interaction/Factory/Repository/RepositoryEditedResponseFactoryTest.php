<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Response\Interaction\Factory\Repository;

use App\Entity\Repository;
use App\Slack\Response\Interaction\Factory\Repository\RepositoryEditedResponseFactory;
use App\Tests\Unit\Slack\WithBlockAssertions;
use App\Tests\Unit\Slack\WithTableAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RepositoryEditedResponseFactory::class)]
class RepositoryEditedResponseFactoryTest extends KernelTestCase
{
    use WithBlockAssertions;
    use WithTableAssertions;

    #[Test]
    public function itShouldCreateSlackPrivateMessageWithItalicCellIfNoUrlProvided(): void
    {
        $factory = new RepositoryEditedResponseFactory();

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($name = 'name');

        $repository->expects($this->once())
            ->method('getUrl')
            ->willReturn(null);

        $result = $factory->create($repository)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'Repository `name` edited successfully.',
            $blocks[0],
        );
        $table = $blocks[1];

        $rows = $this->assertTableRowCount($table, 2);

        $firstRow = $this->getTableRow($rows, 0);

        $this->assertRawTextCell(
            $this->getRowCell($firstRow, 0),
            'Name',
        );
        $this->assertRawTextCell(
            $this->getRowCell($firstRow, 1),
            'URL',
        );

        $secondRow = $this->getTableRow($rows, 1);

        $this->assertRawTextCell($this->getRowCell($secondRow, 0), $name);
        $this->assertItalicTextCell($this->getRowCell($secondRow, 1), 'URL not set');
    }

    #[Test]
    public function itShouldCreateSlackPrivateMessageWithUrlIfProvided(): void
    {
        $factory = new RepositoryEditedResponseFactory();

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($name = 'name');

        $repository->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturn($url = 'url');

        $result = $factory->create($repository)->toArray();

        $this->assertArrayHasKey('blocks', $result);
        $this->assertIsArray($blocks = $result['blocks']);
        $this->assertCount(2, $blocks);

        $this->assertSectionBlockCorrectlyFormatted(
            'Repository `name` edited successfully.',
            $blocks[0],
        );
        $table = $blocks[1];

        $rows = $this->assertTableRowCount($table, 2);

        $firstRow = $this->getTableRow($rows, 0);

        $this->assertRawTextCell(
            $this->getRowCell($firstRow, 0),
            'Name',
        );
        $this->assertRawTextCell(
            $this->getRowCell($firstRow, 1),
            'URL',
        );

        $secondRow = $this->getTableRow($rows, 1);

        $this->assertRawTextCell($this->getRowCell($secondRow, 0), $name);
        $this->assertLinkCell($this->getRowCell($secondRow, 1), $url);
    }
}
