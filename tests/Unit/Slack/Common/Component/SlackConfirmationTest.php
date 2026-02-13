<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Common\Component;

use App\Slack\Common\Component\SlackConfirmation;
use App\Slack\Common\Style;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(SlackConfirmation::class)]
class SlackConfirmationTest extends KernelTestCase
{
    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $confirmation = new SlackConfirmation(
            $title = 'title',
            $text = 'text',
            $confirm = 'confirm',
            $deny = 'deny',
            $style = Style::PRIMARY,
        );

        $result = $confirmation->toArray();

        $this->assertArrayHasKey('title', $result);
        $this->assertisArray($titleElement = $result['title']);
        $this->assertArrayHasKey('text', $titleElement);
        $this->assertEquals($title, $titleElement['text']);
        $this->assertArrayHasKey('type', $titleElement);
        $this->assertEquals('plain_text', $titleElement['type']);

        $this->assertArrayHasKey('text', $result);
        $this->assertisArray($textElement = $result['text']);
        $this->assertArrayHasKey('text', $textElement);
        $this->assertEquals($text, $textElement['text']);
        $this->assertArrayHasKey('type', $textElement);
        $this->assertEquals('plain_text', $textElement['type']);

        $this->assertArrayHasKey('confirm', $result);
        $this->assertisArray($confirmElement = $result['confirm']);
        $this->assertArrayHasKey('text', $confirmElement);
        $this->assertEquals($confirm, $confirmElement['text']);
        $this->assertArrayHasKey('type', $confirmElement);
        $this->assertEquals('plain_text', $confirmElement['type']);

        $this->assertArrayHasKey('deny', $result);
        $this->assertisArray($denyElement = $result['deny']);
        $this->assertArrayHasKey('text', $denyElement);
        $this->assertEquals($deny, $denyElement['text']);
        $this->assertArrayHasKey('type', $denyElement);
        $this->assertEquals('plain_text', $denyElement['type']);

        $this->assertArrayHasKey('style', $result);
        $this->assertEquals($style->value, $result['style']);
    }
}
