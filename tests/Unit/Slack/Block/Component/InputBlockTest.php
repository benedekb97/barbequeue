<?php

declare(strict_types=1);

namespace App\Tests\Unit\Slack\Block\Component;

use App\Slack\Block\Block;
use App\Slack\Block\Component\InputBlock;
use App\Slack\BlockElement\Component\SlackBlockElement;
use App\Tests\Unit\Slack\WithBlockAssertions;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(InputBlock::class)]
class InputBlockTest extends KernelTestCase
{
    use WithBlockAssertions;

    private Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    #[Test]
    public function itShouldReturnCorrectType(): void
    {
        $block = new InputBlock(
            'label',
            $this->createStub(SlackBlockElement::class)
        );

        $this->assertEquals(Block::INPUT, $block->getType());
    }

    #[Test]
    public function itShouldMapCorrectlyToArray(): void
    {
        $element = $this->createMock(SlackBlockElement::class);
        $element->expects($this->once())->method('toArray')->willReturn(
            $elementValue = ['element'],
        );

        $block = new InputBlock(
            $label = $this->faker->word(),
            $element,
            $dispatchAction = $this->faker->boolean(),
            $blockId = $this->faker->boolean() ? $this->faker->word() : null,
            $hint = $this->faker->boolean() ? $this->faker->word() : null,
            $optional = $this->faker->boolean(),
        );

        $this->assertInputBlockCorrectlyFormatted(
            $block->toArray(),
            $label,
            $elementValue,
            $dispatchAction,
            $blockId,
            $hint,
            $optional
        );
    }
}
