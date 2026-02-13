<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component\Table;

use App\Slack\Block\Component\Table\TableCell;
use App\Slack\Block\Component\Table\TableRow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(TableRow::class)]
class TableRowTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $cell = $this->createMock(TableCell::class);
        $cell->expects($this->once())
            ->method('toArray')
            ->willReturn($cellValue = ['cell']);

        $row = new TableRow([$cell]);

        $result = $row->toArray();

        $this->assertCount(1, $result);
        $this->assertEquals($cellValue, $result[0]);
    }
}
