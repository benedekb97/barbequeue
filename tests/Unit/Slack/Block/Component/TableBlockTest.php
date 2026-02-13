<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\Block\Component\Table\LinkCell;
use App\Slack\Block\Component\Table\RawTextCell;
use App\Slack\Block\Component\Table\TableRow;
use App\Slack\Block\Component\Table\UserCell;
use App\Slack\Block\Component\TableBlock;
use App\Tests\Unit\Slack\WithBlockAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(TableBlock::class)]
class TableBlockTest extends KernelTestCase
{
    use WithBlockAssertions;

    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $block = new TableBlock([]);

        $this->assertEquals(Block::TABLE, $block->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $block = new TableBlock(
            [
                new TableRow([
                    new RawTextCell('text'),
                    new UserCell('userId'),
                    new LinkCell('link', 'linkText'),
                ]),
            ],
            $blockId = 'blockId',
            $columnSettings = ['columnSettings'],
        );

        $this->assertTableBlockCorrectlyFormatted(
            [
                [
                    [
                        'type' => 'raw_text',
                        'text' => 'text',
                    ], [
                        'type' => 'rich_text',
                        'elements' => [
                            [
                                'type' => 'rich_text_section',
                                'elements' => [
                                    [
                                        'type' => 'user',
                                        'user_id' => 'userId',
                                    ],
                                ],
                            ],
                        ],
                    ], [
                        'type' => 'rich_text',
                        'elements' => [
                            [
                                'type' => 'rich_text_section',
                                'elements' => [
                                    [
                                        'type' => 'link',
                                        'url' => 'link',
                                        'text' => 'linkText',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $block->toArray(),
            $blockId,
            $columnSettings
        );
    }
}
