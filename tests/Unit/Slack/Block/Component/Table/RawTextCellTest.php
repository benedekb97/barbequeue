<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component\Table;

use App\Slack\Block\Component\Table\RawTextCell;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RawTextCell::class)]
class RawTextCellTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $cell = new RawTextCell($text = 'text');

        $result = $cell->toArray();

        $this->assertArrayHasKey('text', $result);
        $this->assertEquals($text, $result['text']);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('raw_text', $result['type']);
    }
}
