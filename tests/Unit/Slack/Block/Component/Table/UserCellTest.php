<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component\Table;

use App\Slack\Block\Component\Table\UserCell;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(UserCell::class)]
class UserCellTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $cell = new UserCell($userId = 'userId');

        $result = $cell->toArray();

        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('rich_text', $result['type']);

        $this->assertArrayHasKey('elements', $result);
        $this->assertIsArray($elements = $result['elements']);
        $this->assertCount(1, $elements);

        $this->assertIsArray($section = $elements[0]);
        $this->assertArrayHasKey('type', $section);
        $this->assertEquals('rich_text_section', $section['type']);

        $this->assertArrayHasKey('elements', $section);
        $this->assertIsArray($elements = $section['elements']);

        $this->assertCount(1, $elements);
        $this->assertIsArray($result = $elements[0]);

        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('user', $result['type']);

        $this->assertArrayHasKey('user_id', $result);
        $this->assertEquals($userId, $result['user_id']);
    }
}
